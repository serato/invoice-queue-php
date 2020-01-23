<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\SqsQueue;
use Serato\InvoiceQueue\Invoice;
use Serato\InvoiceQueue\InvoiceValidator;
use Aws\Result;
use Aws\Exception\AwsException;
use Aws\Sqs\Exception\SqsException;

class SqsQueueTest extends AbstractTestCase
{
    /**
     * Tests the SqsQueue::getQueueUrl happy path when the queues are already created and
     * can simply return the queue URL in a single API call.
     */
    public function testGetQueueUrlQueueCreated()
    {
        $queueUrl = 'my-invoice-queue-message-url';
        $results = [
            new Result(['QueueUrl'  => $queueUrl])
        ];
        $sqsQueue = new SqsQueue($this->getMockedAwsSdk($results)->createSqs(['version' => '2012-11-05']), 'test');
        $this->assertEquals($queueUrl, $sqsQueue->getQueueUrl());
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());
    }

    /**
     * Tests that the appropriate API calls when SqsQueue::getQueueUrl is called and the queues don't exist.
     */
    public function testGetQueueUrlViaQueueCreation()
    {
        $queueUrl = 'my-invoice-queue-message-url';

        $sqsClient = $this->getMockedAwsSdk()->createSqs(['version' => '2012-11-05']);
        $cmd = $sqsClient->getCommand('GetQueueUrl', [
            'QueueName' => 'my-queue-name'
        ]);

        $results = [
            # Exception for inital GetQueueUrl command
            new SqsException('No Attribute MD5 found', $cmd, ['code' => 'AWS.SimpleQueueService.NonExistentQueue']),
            # Result for CreateQueue command for creation of dead letter queue
            new Result(['QueueUrl'  => 'my-deadletter-queue-url']),
            # Result for GetQueueAttributes command (to fetch ARN of dead letter queue)
            new Result(['Attributes'  => ['QueueArn' => 'arn:deadletter-queue']]),
            # Result for creation of invoice message queue
            new Result(['QueueUrl'  => $queueUrl])
        ];

        $sqsQueue = new SqsQueue($this->getMockedAwsSdk($results)->createSqs(['version' => '2012-11-05']), 'test');
        $this->assertEquals($queueUrl, $sqsQueue->getQueueUrl());
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());
    }

    /**
     * Tests the SqsQueue::sendInvoice method with a valid Invoice and a successful SQS API call
     *
     * @dataProvider sendInvoiceProvider
     */
    public function testSendInvoiceValidInvoiceSuccessfulDelivery($validator)
    {
        $messageId = '123abc456';
    
        $invoice = new Invoice;
        $invoice->setData($this->getValidInvoiceData(), new InvoiceValidator);

        $results = [
            # Result for GetQueueUrl command
            new Result(['QueueUrl'  => 'my-queue-url']),
            # Result for SendMessage command
            new Result(['MessageId'  => $messageId])
        ];

        $sqsQueue = new SqsQueue($this->getMockedAwsSdk($results)->createSqs(['version' => '2012-11-05']), 'test');
        
        $this->assertEquals($messageId, $sqsQueue->sendInvoice($invoice, $validator));
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());
    }

    /**
     * Tests the SqsQueue::sendInvoice method with a valid Invoice and an unsuccessful SQS API call
     *
     * @dataProvider sendInvoiceProvider
     * @expectedException \Serato\InvoiceQueue\Exception\QueueSendException
     */
    public function testSendInvoiceValidInvoiceUnsuccessfulDelivery($validator)
    {
        $invoice = new Invoice;
        $invoice->setData($this->getValidInvoiceData(), new InvoiceValidator);

        $sqsClient = $this->getMockedAwsSdk()->createSqs(['version' => '2012-11-05']);
        $cmd = $sqsClient->getCommand('SendMessage', [
            'QueueName' => 'my-queue-name'
        ]);

        $results = [
            # Result for GetQueueUrl command
            new Result(['QueueUrl'  => 'my-queue-url']),
            # Result for SendMessage command
            new AwsException('Exception message', $cmd)
        ];

        $sqsQueue = new SqsQueue($this->getMockedAwsSdk($results)->createSqs(['version' => '2012-11-05']), 'test');
        $sqsQueue->sendInvoice($invoice, $validator);
    }

    /**
     * Tests the SqsQueue::sendInvoice method with an invalid Invoice
     *
     * @dataProvider sendInvoiceProvider
     * @expectedException \Serato\InvoiceQueue\Exception\ValidationException
     */
    public function testSendInvoiceInvalidInvoice($validator)
    {
        # Don't set any properties = invalid.
        $invoice = new Invoice;
        $sqsQueue = new SqsQueue($this->getMockedAwsSdk()->createSqs(['version' => '2012-11-05']), 'test');
        $this->assertEquals($messageId, $sqsQueue->sendInvoice($invoice, $validator));
    }

    public function sendInvoiceProvider(): array
    {
        return [[null], [new InvoiceValidator]];
    }

    private function getValidInvoiceData()
    {
        return  [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                [
                    'sku' => 'SKU1',
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ]
            ]
        ];
    }

    // private function getInvalidInvoiceData()
    // {
    //     return  [
    //         # 'source' => 'SwsEc', # Missing required field
    //         'invoice_id' => 'A STRING VAL',
    //         'invoice_date' => '2020-01-21T08:54:09Z',
    //         'transaction_reference' => 'A STRING VAL',
    //         'moneyworks_debtor_code' => 'WEBC001',
    //         'subscription_id' => 'A STRING VAL',
    //         'currency' => 'USD',
    //         'gross_amount' => '0', # Wrong data type
    //         'billing_address' => [
    //             'company_name' => 'Company Inc',
    //             'person_name' => 'Jo Bloggs',
    //             'address_1' => '123 Street Road',
    //             'address_2' => 'Suburbia',
    //             'address_3' => 'The Stixx',
    //             'city' => 'Townsville',
    //             'region' => 'Statey',
    //             'post_code' => '90210',
    //             'country_iso' => 'NZ'
    //         ],
    //         'items' => [
    //             [
    //                 # 'sku' => 'SKU1', # Missing required field
    //                 'quantity' => 1,
    //                 'amount_gross' => 0,
    //                 'amount_tax' => 0,
    //                 'amount_net' => 0,
    //                 'unit_price' => 0,
    //                 'tax_code' => 'V'
    //             ]
    //         ]
    //     ];
    // }
}
