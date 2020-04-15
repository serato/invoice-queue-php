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
    public const LOG_RC_SQS_MESSAGE_INVOICE_SEND_SUCCESS = 1000;
    public const LOG_RC_SQS_MESSAGE_INVOICE_SEND_EXCEPTION = 1001;
    public const LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_SUCCESS = 1002;
    public const LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_FAILED = 1003;
    public const LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_EXCEPTION = 1004;

    public const SEND_BATCH_SIZE = 10;

    /** @var SqsClient */
    private $sqsClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $queueName;

    /** @var string */
    private $deadLetterQueueName;

    /** @var string */
    private $queueUrl;

    /** @var string */
    private $deadLetterQueueUrl;

    /** @var array */
    private $messageBatch = [
        'invoices' => [],
        'sqsParams' => []
    ];

    /** @var callable */
    private $onSendMessageBatch;

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
        $this->deadLetterQueueName = 'InvoiceData-' . $queueEnv . '-DeadLetter.fifo';
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
                self::LOG_RC_SQS_MESSAGE_INVOICE_SEND_SUCCESS,
                '[' . $invoice->getInvoiceId() . '] - SQS message - Send success',
                [
                    'invoice_id' => $invoice->getInvoiceId(),
                    'caller' => __METHOD__,
                    'sqs_message_id' => $result['MessageId']
                ]
            );
            return $result['MessageId'];
        } catch (AwsException $e) {
            $this->logQueueSendResult(
                'ALERT',
                self::LOG_RC_SQS_MESSAGE_INVOICE_SEND_EXCEPTION,
                '[' . $invoice->getInvoiceId() . '] - SQS message - Send failure',
                [
                    'invoice_id' => $invoice->getInvoiceId(),
                    'caller' => __METHOD__,
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
        $this->messageBatch['invoices'][$invoice->getInvoiceId()] = $invoice;
        $this->messageBatch['sqsParams'][] = $this->invoiceToSqsSendParams(
            $invoice,
            $invoice->getInvoiceId()
        );
        return $this;
    }

    /**
     * Get the primary SQS queue URL
     *
     * @return string
     */
    public function getQueueUrl(): string
    {
        if ($this->queueUrl === null) {
            $this->queueUrl = $this->getSqsQueueUrl(
                $this->getQueueName(),
                function () {
                    # Get the ARN of the dead letter queue
                    $result = $this->getSqsClient()->getQueueAttributes([
                        'AttributeNames' => ['QueueArn'],
                        'QueueUrl' => $this->getDeadLetterQueueUrl()
                    ]);
                    return [
                        'QueueName' => $this->getQueueName(),
                        'Attributes' => [
                            'MessageRetentionPeriod' => 345600, # 4 days
                            'VisibilityTimeout' => 60,
                            'ReceiveMessageWaitTimeSeconds' => 20, # Create queue with long polling enabled
                            'FifoQueue' => 'true',
                            'ContentBasedDeduplication' => 'true',
                            'RedrivePolicy' => json_encode([
                                'deadLetterTargetArn' => $result['Attributes']['QueueArn'],
                                'maxReceiveCount' => 2
                            ])
                        ],
                    ];
                }
            );
        }
        return $this->queueUrl;
    }

    /**
     * Get the dead letter SQS queue URL
     *
     * @return string
     */
    public function getDeadLetterQueueUrl(): string
    {
        if ($this->deadLetterQueueUrl === null) {
            $this->deadLetterQueueUrl = $this->getSqsQueueUrl(
                $this->getDeadLetterQueueName(),
                function () {
                    return [
                        'QueueName' => $this->getDeadLetterQueueName(),
                        'Attributes' => [
                            'MessageRetentionPeriod' => 1209600, # 14 days (the maximum allowed by SQS)
                            'VisibilityTimeout' => 180,
                            'ReceiveMessageWaitTimeSeconds' => 20, # Create queue with long polling enabled
                            'FifoQueue' => 'true'
                        ]
                    ];
                }
            );
        }
        return $this->deadLetterQueueUrl;
    }

    /**
     * Returns the name of the primary SQS message queue
     *
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * Returns the name of the dead letter SQS message queue
     *
     * @return string
     */
    public function getDeadLetterQueueName(): string
    {
        return $this->deadLetterQueueName;
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
     * Sets a callable to be invoked whenever a batch of invoice messages are sent to
     * SQS via the sendMessageBatch method.
     *
     * Any function or class method that implements the Callable interface can be provided.
     *
     * The callable is passed two arguments:
     *
     * - array $successfulInvoices      An array of Serato\InvoiceQueue\Invoice instances that were
     *                                  successfully delivered to SQS.
     * - array $failedInvoices          An array of Serato\InvoiceQueue\Invoice instances that failed
     *                                  to deliver to SQS.
     *
     * @param callable $callable
     * @return self
     */
    public function setOnSendMessageBatchCallback(callable $callable): self
    {
        $this->onSendMessageBatch = $callable;
        return $this;
    }

    /**
     * Returns the PSR logger instance
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Fetches a queue URL from the SQS service. If the queue does not exist it's created.
     *
     * The $createQueueParams parameter is a callable that should return an associative array
     * of parameters suitable for an SQS::CreateQueue API call.
     *
     * @param string $queueName
     * @param callable $createQueueParams
     * @return string
     * @throws SqsException
     */
    private function getSqsQueueUrl(string $queueName, callable $createQueueParams): string
    {
        try {
            $result = $this->getSqsClient()->getQueueUrl([
                'QueueName' => $queueName
            ]);
            return $result['QueueUrl'];
        } catch (SqsException $e) {
            if ($e->getAwsErrorCode() === 'AWS.SimpleQueueService.NonExistentQueue') {
                $result = $this->getSqsClient()->createQueue(call_user_func($createQueueParams));
                return $result['QueueUrl'];
            } else {
                throw $e;
            }
        }
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
                $success = [];
                $failure = [];
                $result = $this->getSqsClient()->sendMessageBatch([
                    'Entries'   => $this->messageBatch['sqsParams'],
                    'QueueUrl'  => $this->getQueueUrl()
                ]);
                if (isset($result['Successful'])) {
                    # Create 1 log entry per invoice in the batch
                    foreach ($result['Successful'] as $resultData) {
                        $invoice = $this->messageBatch['invoices'][$resultData['Id']];
                        $success[] = $invoice;
                        $this->logQueueSendResult(
                            'INFO',
                            self::LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_SUCCESS,
                            '[' . $invoice->getInvoiceId() . '] - SQS message - Batch send success',
                            [
                                'invoice_id' => $invoice->getInvoiceId(),
                                'caller' => __METHOD__
                            ]
                        );
                    }
                }
                if (isset($result['Failed'])) {
                    # Create 1 log entry per invoice in the batch
                    foreach ($result['Failed'] as $resultData) {
                        $invoice = $this->messageBatch['invoices'][$resultData['Id']];
                        $failure[] = $invoice;
                        $this->logQueueSendResult(
                            'ALERT',
                            self::LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_FAILED,
                            '[' . $invoice->getInvoiceId() . '] - SQS message - Batch send failure',
                            [
                                'invoice_id' => $invoice->getInvoiceId(),
                                'caller' => __METHOD__,
                                'aws_failed_result' => $resultData
                            ]
                        );
                    }
                }
                if ($this->onSendMessageBatch !== null && is_callable($this->onSendMessageBatch)) {
                    call_user_func($this->onSendMessageBatch, $success, $failure);
                }
                return $result;
            } catch (AwsException $e) {
                # The entire batch failed :-(
                foreach ($this->messageBatch['invoices'] as $invoiceId => $invoice) {
                    $this->logQueueSendResult(
                        'ALERT',
                        self::LOG_RC_SQS_MESSAGE_INVOICE_SENDBATCH_EXCEPTION,
                        '[' . $invoice->getInvoiceId() . '] - SQS message - Batch send failure',
                        [
                            'invoice_id' => $invoice->getInvoiceId(),
                            'caller' => __METHOD__,
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

    private function logQueueSendResult(string $level, int $resultCode, string $message, array $context): void
    {
        $this->logger->log(
            $level,
            $message,
            array_merge(
                ['result_code' => $resultCode],
                $context,
                ['queue_name' =>$this->getQueueName()]
            )
        );
    }
}
