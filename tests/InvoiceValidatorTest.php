<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\InvoiceValidator;

class InvoiceValidatorTest extends AbstractTestCase
{
    /**
     * Tests the `Serato\InvoiceQueue\InvoiceValidator::validateArray` method
     *
     * @param string|null $ref
     * @param boolean $isValid
     * @param array $data
     * @return void
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(?string $ref, bool $isValid, array $data)
    {
        $validator = new InvoiceValidator;
        $bVal = $validator->validateArray($data, $ref);
        # print_r($validator->getErrors($ref));
        $this->assertEquals($isValid, $bVal);
    }

    public function validateDataProvider()
    {
        $items = [];

        $arrays = [
            include __DIR__ . '/resources/schema_validation_data/line_item.php',
            include __DIR__ . '/resources/schema_validation_data/billing_address.php',
            include __DIR__ . '/resources/schema_validation_data/invoice.php'
        ];

        foreach ($arrays as $array) {
            foreach ($array as $item) {
                array_push($items, $item);
            }
        }

        return $items;
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Exception\JsonDecodeException
     */
    public function testInvalidJsonString()
    {
        $validator = new InvoiceValidator;
        $validator->validateJsonString('');
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Exception\JsonEncodeException
     */
    public function testFailedJsonEncode()
    {
        $fp = fopen(__DIR__ . '/resources/schema_validation_data/line_item.php', 'r');
        $validator = new InvoiceValidator;
        $validator->validateArray(['fp' => $fp]);
    }

    public function testMultipleUsesOfSingleInstance()
    {
        $validator = new InvoiceValidator;

        $fileData = include __DIR__ . '/resources/schema_validation_data/single_instance_test_data.php';

        foreach ($fileData as $invoiceData) {
            $ref = $invoiceData[0];
            $isValid = $invoiceData[1];
            $data = $invoiceData[2];
            $info = $invoiceData[3];
            $this->assertEquals($isValid, $validator->validateArray($data, $ref), $info);
        }
    }
}
