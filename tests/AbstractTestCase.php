<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

// use Serato\SwsSdk\Sdk;
// use GuzzleHttp\Handler\MockHandler;
// use GuzzleHttp\HandlerStack;
// use GuzzleHttp\Psr7\Response;
// use Psr\Http\Message\ResponseInterface;
// use Serato\SwsSdk\Result;
// use Exception;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        // Error reporting as defined in php.ini file
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    }
}
