<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

/**
 * ** SqsQueueCallbackTester **
 *
 * A class for testing the Serato\InvoiceQueue\SqsQueue::onSendMessageBatch
 * functionality.
 */
class SqsQueueCallbackTester extends AbstractTestCase
{
    /** @var Array<mixed> */
    public $successfulInvoices;

    /** @var Array<mixed> */
    public $failedInvoices;
    /**
     * Callable for handling the SqsQueue::onSendMessageBatch callback
     *
     * @param Array<mixed> $successfulInvoices
     * @param Array<mixed> $failedInvoices
     * @return void
     */
    public function __invoke(array $successfulInvoices, array $failedInvoices): void
    {
        $this->successfulInvoices = $successfulInvoices;
        $this->failedInvoices = $failedInvoices;
    }
}
