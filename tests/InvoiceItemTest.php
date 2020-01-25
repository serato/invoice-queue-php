<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\InvoiceItem;
use Serato\InvoiceQueue\InvoiceValidator;

class InvoiceItemTest extends AbstractTestCase
{
    /**
     * Tests the Load method with valid data
     *
     * @return void
     */
    public function testLoadWithValidData()
    {
        $validator = new InvoiceValidator;
        $invoiceItem = InvoiceItem::load($this->getValidInvoiceData(), $validator);
        $this->assertEquals($this->getValidInvoiceData(), $invoiceItem->getData());
    }

    /**
     * Tests the Load method with invalid data
     *
     * @return void
     * @expectedException \Serato\InvoiceQueue\Exception\ValidationException
     */
    public function testLoadWithInvalidData()
    {
        $validator = new InvoiceValidator;
        $invoiceItem = InvoiceItem::load($this->getInvalidInvoiceData(), $validator);
    }

    private function getValidInvoiceData()
    {
        return  [
            'sku' => 'SKU1',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => 'V'
        ];
    }

    private function getInvalidInvoiceData()
    {
        return  [
            # 'sku' => 'SKU1', # Missing required field
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => 'V'
        ];
    }
}
