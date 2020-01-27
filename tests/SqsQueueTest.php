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
use Exception;

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
        $sqsQueue = $this->getSqsQueueInstance($results);
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

        $sqsQueue = $this->getSqsQueueInstance($results);
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
    
        $invoice = Invoice::load($this->getValidInvoiceData()[0], new InvoiceValidator);

        $results = [
            # Result for GetQueueUrl command
            new Result(['QueueUrl'  => 'my-queue-url']),
            # Result for SendMessage command
            new Result(['MessageId'  => $messageId])
        ];

        $sqsQueue = $this->getSqsQueueInstance($results);
        
        $this->assertEquals($messageId, $sqsQueue->sendInvoice($invoice, $validator));
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());

        # Ensure that a log entry is created
        $log = $this->getLogFileContents();

        $this->assertTrue($log !== '');
        $this->assertTrue(strpos($log, '.INFO:') !== false);
        $this->assertTrue(strpos($log, 'SQS sendMessage') !== false);
    }

    /**
     * Tests the SqsQueue::sendInvoice method with a valid Invoice and an unsuccessful SQS API call
     *
     * @dataProvider sendInvoiceProvider
     * @expectedException \Serato\InvoiceQueue\Exception\QueueSendException
     */
    public function testSendInvoiceValidInvoiceUnsuccessfulDelivery($validator)
    {
        $invoice = Invoice::load($this->getValidInvoiceData()[0], new InvoiceValidator);

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

        $sqsQueue = $this->getSqsQueueInstance($results);
        $sqsQueue->sendInvoice($invoice, $validator);
    }

    /**
     * Tests the SqsQueue::sendInvoice method with a valid Invoice and an unsuccessful SQS API call
     *
     * Ensure that a log entry is created
     *
     * @dataProvider sendInvoiceProvider
     */
    public function testSendInvoiceValidInvoiceUnsuccessfulDeliveryLogEntry($validator)
    {
        $invoice = Invoice::load($this->getValidInvoiceData()[0], new InvoiceValidator);

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

        $sqsQueue = $this->getSqsQueueInstance($results);
        try {
            $sqsQueue->sendInvoice($invoice, $validator);
        } catch (Exception $e) {
            # Ignore
        }

        $log = $this->getLogFileContents();

        $this->assertTrue($log !== '');
        $this->assertTrue(strpos($log, '.ALERT:') !== false);
        $this->assertTrue(strpos($log, 'SQS sendMessage') !== false);
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
        $invoice = Invoice::create();
        $sqsQueue = $this->getSqsQueueInstance();
        $sqsQueue->sendInvoice($invoice, $validator);
    }

    public function sendInvoiceProvider(): array
    {
        return [[null], [new InvoiceValidator]];
    }

    /**
     * Tests the SqsQueue::sendToBatch method with a single invoice and that the batch
     * is successfully sent when SqsQueue method is destructed.
     */
    public function testSendInvoiceToBatchSuccessfulSendViaDestructor()
    {
        $validator = new InvoiceValidator;
        $invoice = Invoice::load($this->getValidInvoiceData()[0], $validator);

        $results = [
            # Result for GetQueueUrl command
            new Result(['QueueUrl'  => 'my-queue-url']),
            # Result for SendMessageBatch command
            new Result([])
        ];

        $sqsQueue = $this->getSqsQueueInstance($results);

        $sqsQueue->sendInvoiceToBatch($invoice, $validator);
        # Destroy the object. This should trigger the batch send.
        unset($sqsQueue);

        # Confirm the stack is empty (ie. that the API calls HAVE been made)
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());
    }

    /**
     * Tests the SqsQueue::sendToBatch method with a single invoice and that the batch
     * is unsuccessfully sent when SqsQueue method is destructed.
     *
     * @expectedException \Serato\InvoiceQueue\Exception\QueueSendException
     */
    public function testSendInvoiceToBatchUnsuccessfulSendViaDestructor()
    {
        $validator = new InvoiceValidator;

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

        $sqsQueue = $this->getSqsQueueInstance($results);

        foreach ($this->getValidInvoiceData() as $data) {
            $invoice = Invoice::load($data, $validator);
            $sqsQueue->sendInvoiceToBatch($invoice, $validator);
        }
        # Destroy the object. This should trigger the batch send.
        unset($sqsQueue);
    }


    /**
     * Tests the SqsQueue::sendToBatch method with a single invoice and that the batch
     * is unsuccessfully sent when SqsQueue method is destructed.
     *
     * Test that a log entry is created
     */
    public function testSendInvoiceToBatchUnsuccessfulSendViaDestructorLogEntry()
    {
        $validator = new InvoiceValidator;

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

        $sqsQueue = $this->getSqsQueueInstance($results);

        foreach ($this->getValidInvoiceData() as $data) {
            $invoice = Invoice::load($data, $validator);
            $sqsQueue->sendInvoiceToBatch($invoice, $validator);
        }

        try {
            # Destroy the object. This should trigger the batch send.
            unset($sqsQueue);
        } catch (Exception $e) {
            # Ignore
        }

        $log = trim($this->getLogFileContents());

        $this->assertTrue($log !== '');
        $this->assertTrue(strpos($log, '.ALERT:') !== false);
        $this->assertTrue(strpos($log, 'SQS sendMessageBatch') !== false);
        # 1 log entry per invoice in the batch
        $this->assertEquals(2, count(explode("\n", $log)));
    }

    /**
     * Tests the SqsQueue::sendToBatch method triggers a batch send when the internal batch size
     * reaches the defined send threshold.
     */
    public function testSendInvoiceToBatchSuccessfulSendViaSendThreshold()
    {
        $validator = new InvoiceValidator;
        $invoice = Invoice::load($this->getValidInvoiceData()[0], $validator);

        $results = [
            # Result for GetQueueUrl command
            new Result(['QueueUrl'  => 'my-queue-url']),
            # Result for SendMessageBatch command for first batch of 10
            new Result([]),
            # Result for SendMessageBatch command for the final batch of 1
            new Result([])
        ];

        $sqsQueue = $this->getSqsQueueInstance($results);

        for ($i = 0; $i < (SqsQueue::SEND_BATCH_SIZE + 1); $i++) {
            $sqsQueue->sendInvoiceToBatch($invoice, $validator);
        }
        
        # Destroy the object. This should trigger the final batch send.
        unset($sqsQueue);

        # Confirm the stack is empty (ie. that the API calls HAVE been made)
        $this->assertEquals(0, $this->getAwsMockHandlerStackCount());
    }

    private function getSqsQueueInstance(array $results = []): SqsQueue
    {
        return new SqsQueue(
            $this->getMockedAwsSdk($results)->createSqs(['version' => '2012-11-05']),
            'test',
            $this->getLogger()
        );
    }

    private function getValidInvoiceData()
    {
        return [
            [
                'source' => 'SwsEc',
                'invoice_id' => 'INV-1234ABCD',
                'invoice_date' => '2020-01-21T08:54:09Z',
                'order_id' => '1234567',
                'transaction_reference' => 'REF-ABCD1234',
                'payment_provider' => 'BT',
                'moneyworks_debtor_code' => 'WEBC001',
                'subscription_id' => 'SUB-XYZ-ABC',
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
                        'quantity' => 2,
                        'amount_gross' => 2200,
                        'amount_tax' => 200,
                        'amount_net' => 2000,
                        'unit_price' => 1000,
                        'tax_code' => 'V'
                    ]
                ]
            ],
            [
                'source' => 'SwsEc',
                'invoice_id' => 'INV-ABCD-1234',
                'invoice_date' => '2020-01-21T08:54:09Z',
                'order_id' => '1234568',
                'transaction_reference' => 'REF-1234ABCD',
                'payment_provider' => 'BT',
                'moneyworks_debtor_code' => 'WEBC001',
                'subscription_id' => 'SUB-ABC-XYZ',
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
                        'amount_gross' => 1100,
                        'amount_tax' => 100,
                        'amount_net' => 1000,
                        'unit_price' => 1000,
                        'tax_code' => 'V'
                    ]
                ]
            ]
        ];
    }
}
