<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\MessageValidator;

class MessageValidatorTest extends AbstractTestCase
{
    /**
     * Tests the `Serato\InvoiceQueue\MessageValidator::validateArray` method
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
        $validator = new MessageValidator;
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
}