<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Exception;

use RuntimeException;

class JsonEncodeException extends RuntimeException
{
    protected $message = 'JSON encode error. Unable to JSON encode data.';
}
