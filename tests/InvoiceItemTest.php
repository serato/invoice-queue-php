<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Exception\ValidationException;
use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\Invoice;
use Serato\InvoiceQueue\InvoiceItem;
use Serato\InvoiceQueue\InvoiceValidator;

class InvoiceItemTest extends AbstractTestCase
{
    /**
     * Tests the Load method with valid data
     *
     * @return void
     */
    public function testLoadWithValidData(): void
    {
        $validator = new InvoiceValidator();
        $invoiceItem = InvoiceItem::load($this->getValidInvoiceData(), $validator);
        $this->assertEquals($this->getValidInvoiceData(), $invoiceItem->getData());
    }

    /**
     * Tests the Load method with invalid data
     *
     * @return void
     */
    public function testLoadWithInvalidData(): void
    {
        $this->expectException(ValidationException::class);
        $validator = new InvoiceValidator();
        $invoiceItem = InvoiceItem::load($this->getInvalidInvoiceData(), $validator);
    }

    /**
     * @return Array<mixed>
     */
    private function getValidInvoiceData(): array
    {
        return  [
            'sku' => 'SKU1',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ];
    }

    /**
     * @return Array<mixed>
     */
    private function getInvalidInvoiceData(): array
    {
        return  [
            # 'sku' => 'SKU1', # Missing required field
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ];
    }
}
