<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

use Aws\Sqs\SqsClient;
use Aws\Sqs\Exception\SqsException;
use Exception;

/**
 * ** SQS Queue **
 *
 * Functionality for interacting with the underlying SQS message queues.
 */
class SqsQueue
{
    /** @var SqsClient */
    private $sqsClient;

    /** @var string */
    private $queueName;

    /** @var string */
    private $queueUrl;

    /**
     * Constructs the object
     *
     * @param SqsClient $sqsClient  A SqsClient instance from the AWS SDK
     * @param string $queueEnv      One of `test` or `production`
     */
    public function __construct(SqsClient $sqsClient, string $queueEnv)
    {
        $this->sqsClient = $sqsClient;
        if ($queueEnv !== 'test' && $queueEnv !== 'production') {
            throw new Exception(
                "Invalid `queueEnv` value '" . $queueEnv . "'. Valid values are 'test' or 'production'"
            );
        }
        # FIFO queue names MUST end in ".fifo"
        $this->queueName = 'InvoiceData-' . $queueEnv . '.fifo';
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
}
