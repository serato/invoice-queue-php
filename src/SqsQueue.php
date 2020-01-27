<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

use Aws\Sqs\SqsClient;
use Aws\Result;
use Aws\Exception\AwsException;
use Aws\Sqs\Exception\SqsException;
use Serato\InvoiceQueue\Exception\ValidationException;
use Serato\InvoiceQueue\Exception\QueueSendException;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * ** SQS Queue **
 *
 * Functionality for interacting with the underlying SQS message queues.
 */
class SqsQueue
{
    public const SEND_BATCH_SIZE = 10;

    /** @var SqsClient */
    private $sqsClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $queueName;

    /** @var string */
    private $queueUrl;

    /** @var array */
    private $messageBatch = [
        'invoices' => [],
        'sqsParams' => []
    ];

    /**
     * Constructs the object
     *
     * @param SqsClient         $sqsClient      A SqsClient instance from the AWS SDK
     * @param string            $queueEnv       One of `test` or `production`
     * @param LoggerInterface   $logger         PSR logger interface instance
     */
    public function __construct(
        SqsClient $sqsClient,
        string $queueEnv,
        LoggerInterface $logger
    ) {
        $this->sqsClient = $sqsClient;
        $this->logger = $logger;
        if ($queueEnv !== 'test' && $queueEnv !== 'production') {
            throw new Exception(
                "Invalid `queueEnv` value '" . $queueEnv . "'. Valid values are 'test' or 'production'"
            );
        }

        # FIFO queue names MUST end in ".fifo"
        $this->queueName = 'InvoiceData-' . $queueEnv . '.fifo';
    }

    public function __destruct()
    {
        $this->sendMessageBatch();
    }

    /**
     * Sends a single Invoice to the message queue. Returns an SQS message ID.
     *
     * @param Invoice $invoice
     * @param InvoiceValidator|null $validator
     * @return string
     * @throws ValidationException
     */
    public function sendInvoice(Invoice $invoice, ?InvoiceValidator $validator = null): string
    {
        if ($validator === null) {
            $validator = new InvoiceValidator;
        }
        if (!$validator->validateArray($invoice->getData())) {
            throw new ValidationException($validator->getErrors());
        }
        try {
            $result = $this->getSqsClient()->sendMessage($this->invoiceToSqsSendParams($invoice));
            $this->logQueueSendResult(
                'INFO',
                $invoice->getInvoiceId() . ' SQS sendMessage success',
                [
                    'invoice_id' => $invoice->getInvoiceId(),
                    'sqs_message_id' => $result['MessageId'],
                    'caller' => __METHOD__
                ],
                ['aws_result' => $result->toArray()]
            );
            return $result['MessageId'];
        } catch (AwsException $e) {
            $this->logQueueSendResult(
                'ALERT',
                $invoice->getInvoiceId() . ' SQS sendMessage failure',
                [
                    'invoice_id' => $invoice->getInvoiceId(),
                    'caller' => __METHOD__
                ],
                [
                    'aws_exception' => [
                        'message' => $e->getMessage(),
                        'extra' => $e->toArray()
                    ]
                ]
            );
            $this->throwQueueSendException($e);
        }
    }

    /**
     * Adds an invoice to a batch for future batch delivery to message queue.
     *
     * @param Invoice $invoice
     * @param InvoiceValidator $validator
     * @return self
     * @throws ValidationException
     */
    public function sendInvoiceToBatch(Invoice $invoice, InvoiceValidator $validator): self
    {
        if (!$validator->validateArray($invoice->getData())) {
            throw new ValidationException($validator->getErrors());
        }
        if (count($this->messageBatch['invoices']) === self::SEND_BATCH_SIZE) {
            $this->sendMessageBatch();
        }
        $this->messageBatch['invoices'][] = $invoice;
        $this->messageBatch['sqsParams'][] = $this->invoiceToSqsSendParams(
            $invoice,
            (string)count($this->messageBatch)
        );
        return $this;
    }

    /**
     * Get the SQS queue URL
     *
     * @return string
     */
    public function getQueueUrl(): string
    {
        if ($this->queueUrl === null) {
            try {
                $result = $this->getSqsClient()->getQueueUrl([
                    'QueueName' => $this->getQueueName()
                ]);
                $this->queueUrl = $result['QueueUrl'];
            } catch (SqsException $e) {
                if ($e->getAwsErrorCode() === 'AWS.SimpleQueueService.NonExistentQueue') {
                    #   **********************************************
                    #   *** The queue doesn't exist. So create it. ***
                    #   **********************************************
                    # First, create the dead letter queue
                    $result = $this->getSqsClient()->createQueue([
                        'QueueName' => rtrim($this->getQueueName(), '.fifo') . '-DeadLetter.fifo',
                        'Attributes' => [
                            'MessageRetentionPeriod' => 1209600, # 14 days (the maximum allowed by SQS)
                            'VisibilityTimeout' => 180,
                            'ReceiveMessageWaitTimeSeconds' => 20, # Create queue with long polling enabled
                            'FifoQueue' => 'true'
                        ],
                    ]);
                    $deadLetterQueueUrl = $result['QueueUrl'];
                    # Now we need to get the ARN of the dead letter queue
                    $result = $this->getSqsClient()->getQueueAttributes([
                        'AttributeNames' => ['QueueArn'],
                        'QueueUrl' => $deadLetterQueueUrl
                    ]);
                    $deadLetterArn = $result['Attributes']['QueueArn'];
                    # Create the main queue
                    $result = $this->getSqsClient()->createQueue([
                        'QueueName' => $this->getQueueName(),
                        'Attributes' => [
                            'MessageRetentionPeriod' => 345600, # 4 days
                            'VisibilityTimeout' => 60,
                            'ReceiveMessageWaitTimeSeconds' => 20, # Create queue with long polling enabled
                            'FifoQueue' => 'true',
                            'ContentBasedDeduplication' => 'true',
                            'RedrivePolicy' => json_encode([
                                'deadLetterTargetArn' => $deadLetterArn,
                                'maxReceiveCount' => 5
                            ])
                        ],
                    ]);
                    $this->queueUrl = $result['QueueUrl'];
                } else {
                    throw $e;
                }
            }
        }
        return $this->queueUrl;
    }

    /**
     * Returns the name of SQS message queue
     *
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * Returns the SQS queue client
     *
     * @return SqsClient
     */
    public function getSqsClient(): SqsClient
    {
        return $this->sqsClient;
    }

    /**
     * Converts an Invoice into a param array suitable for sending to an SQS queue.
     *
     * @param Invoice   $invoice
     * @param string    $batchMessageId     An ID that is unique within a batch of messages
     *                                      (required for batch operations)
     * @return array
     */
    private function invoiceToSqsSendParams(Invoice $invoice, string $batchMessageId = null): array
    {
        return array_merge(
            [
                'MessageAttributes' => [
                    'InvoiceSource' => [
                        'DataType'      => 'String',
                        'StringValue'   => $invoice->getSource()
                    ],
                    'InvoiceId' => [
                        'DataType'      => 'String',
                        'StringValue'   => $invoice->getInvoiceId()
                    ]
                ],
                'MessageBody' => json_encode($invoice->getData()),
                'MessageDeduplicationId' => $invoice->getInvoiceId(),
                'MessageGroupId' => $invoice->getSource()
            ],
            // If message is NOT part of batch add the queue URL
            // If it IS part of batch add the message ID
            ($batchMessageId === null ?
                ['QueueUrl' => $this->getQueueUrl()] :
                ['Id' => $batchMessageId]
            )
        );
    }

    /**
     * @return Result | null
     */
    private function sendMessageBatch(): ?Result
    {
        if (count($this->messageBatch['invoices']) > 0) {
            try {
                $result = $this->getSqsClient()->sendMessageBatch([
                    'Entries'   => $this->messageBatch['sqsParams'],
                    'QueueUrl'  => $this->getQueueUrl()
                ]);
                return $result;
            } catch (AwsException $e) {
                foreach ($this->messageBatch['invoices'] as $invoice) {
                    $this->logQueueSendResult(
                        'ALERT',
                        $invoice->getInvoiceId() . ' SQS sendMessageBatch failure',
                        [
                            'invoice_id' => $invoice->getInvoiceId(),
                            'caller' => __METHOD__
                        ],
                        [
                            'aws_exception' => [
                                'message' => $e->getMessage(),
                                'extra' => $e->toArray()
                            ]
                        ]
                    );
                }
                $this->throwQueueSendException($e);
            }
            # Reset the batch even in event of a failure otherwise we'll
            # keep retrying indefinitely.
            $this->messageBatch = [
                'invoices' => [],
                'sqsParams' => []
            ];
        }
        return null;
    }

    /**
     * Throws a `QueueSendException` exception in response to catching an
     * `AwsException` exception.
     *
     * Uses the`AwsException` instance to create a meaningful error message when
     * throwing the `QueueSendException` exception.
     *
     * @throws QueueSendException
     */
    private function throwQueueSendException(AwsException $e)
    {
        $msg = 'Error sending message to SQS queue `' . $this->getQueueName() .
                '`.' . PHP_EOL .
                'The AWS SDK threw an exception with the following details:' . PHP_EOL .
                'Exception class: ' . get_class($e) . PHP_EOL .
                'Exception message: ' . $e->getMessage();

        if ($e->getAwsErrorMessage() !== null) {
            $msg .= PHP_EOL . 'AWS error message: ' . $e->getAwsErrorMessage();
        }
        if ($e->getAwsErrorType() !== null) {
            $msg .= PHP_EOL . 'AWS error type: ' . $e->getAwsErrorType();
        }
        if ($e->getAwsErrorCode() !== null) {
            $msg .= PHP_EOL . 'AWS error code: ' . $e->getAwsErrorCode();
        }

        throw new QueueSendException($msg);
    }

    //private function

    private function logQueueSendResult(string $level, string $message, array $context, array $extra = []): void
    {
        $this->logger->log(
            $level,
            $message,
            array_merge(
                $context,
                ['queue_name' =>$this->getQueueName()],
                $extra
            )
        );
    }
}
