<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Aws\Sdk;
use Aws\MockHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    const LOG_FILE_PATH = '/srv/www/shared/phpunit.log';

    /** @var MockHandler */
    private $mockHandler;

    /** @var string */
    private $logFilePath = '';

    /** @var Logger */
    private $logger;

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

    protected function setUp()
    {
        $this->logFilePath = sys_get_temp_dir() . '/php-unit-log.log';
        $this->logger = new Logger("PHP-Unit-Logger");
        $this->logger->pushHandler(new StreamHandler($this->logFilePath, Logger::DEBUG));
    }

    protected function tearDown()
    {
        if (file_exists($this->getLogFilePath())) {
            unlink($this->getLogFilePath());
        }
    }

    protected function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    protected function getLogFileContents(): string
    {
        $log = file_get_contents($this->getLogFilePath());
        return $log === false ? '' : $log;
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

    /**
     * Returns an `Psr\LoggerInterface` instance
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
