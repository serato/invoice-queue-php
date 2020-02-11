<?php
namespace Serato\InvoiceQueue;

use Monolog\Formatter\JsonFormatter;
use DateTime;

/**
 * ** Log Formatter **
 *
 * Takes the Monolog JSON formatter and prepends an ISO8601 timestamp to the record.
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
        return '[' . $record['datetime']->format(DateTime::ATOM) . '] ' . parent::format($record);
    }
}
