<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Exception;

use RuntimeException;

class JsonDecodeException extends RuntimeException
{
    protected $message = 'JSON decode error. Cannot JSON decode string.';
}
