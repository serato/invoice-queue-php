<?php
namespace Serato\InvoiceQueue;

use Monolog\Formatter\JsonFormatter;

/**
 * ** Log Formatter **
 *
 * Takes the Monolog JSON formatter and prepends a formatted timestamp to the record.
 *
 * This makes the record more legible in Cloudwatch Log.
 */
class MonologLogFormatter extends JsonFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record): string
    {
        return '[' . $record['datetime']->format('Y-m-d H:i:s') . '] ' . parent::format($record);
    }
}
