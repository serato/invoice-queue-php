<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Aws\Sdk;
use Aws\MockHandler;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /** @var MockHandler */
    private $mockHandler;

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

    /**
     * @param array $mockResults    An array of mock results to return from SDK clients
     * @return Sdk
     */
    protected function getMockedAwsSdk(array $mockResults = []): Sdk
    {
        $this->mockHandler = new MockHandler();
        foreach ($mockResults as $result) {
            $this->mockHandler->append($result);
        }
        return new Sdk([
            'region' => 'us-east-1',
            'version' => '2014-11-01',
            'credentials' => [
                'key' => 'my-access-key-id',
                'secret' => 'my-secret-access-key'
            ],
            'handler' => $this->mockHandler
        ]);
    }

    /**
     * Returns the number of remaining items in the AWS mock handler queue.
     *
     * @return int
     */
    protected function getAwsMockHandlerStackCount()
    {
        return $this->mockHandler->count();
    }
}
