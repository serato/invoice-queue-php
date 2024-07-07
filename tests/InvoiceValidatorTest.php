<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use JsonSchema\Exception\JsonDecodingException;
use Serato\InvoiceQueue\Exception\JsonDecodeException;
use Serato\InvoiceQueue\Exception\JsonEncodeException;
use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\InvoiceValidator;

class InvoiceValidatorTest extends AbstractTestCase
{
    /**
     * Tests the `Serato\InvoiceQueue\InvoiceValidator::validateArray` method
     *
     * @param string|null $ref
     * @param boolean $isValid
     * @param Array<mixed> $data
     * @return void
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(?string $ref, bool $isValid, array $data): void
    {
        $validator = new InvoiceValidator();
        $bVal = $validator->validateArray($data, $ref);
        # print_r($validator->getErrors($ref));
        $this->assertEquals($isValid, $bVal);
    }

    /**
     * @return Array<mixed>
     */
    public function validateDataProvider(): array
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
     * @return void
     */
    public function testInvalidJsonString(): void
    {
        $this->expectException(JsonDecodeException::class);
        $validator = new InvoiceValidator();
        $validator->validateJsonString('');
    }

    /**
     * @return void
     */
    public function testFailedJsonEncode(): void
    {
        $this->expectException(JsonEncodeException::class);
        $fp = fopen(__DIR__ . '/resources/schema_validation_data/line_item.php', 'r');
        $validator = new InvoiceValidator();
        $validator->validateArray(['fp' => $fp]);
    }

    /**
     * @return void
     */
    public function testMultipleUsesOfSingleInstance(): void
    {
        $validator = new InvoiceValidator();

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
