<?php

namespace Serato\InvoiceQueue\Exception;

use RuntimeException;

class JsonDecodeException extends RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'JSON decode error. Cannot JSON decode string.';
}
