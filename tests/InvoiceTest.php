<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\Invoice;
use Serato\InvoiceQueue\InvoiceValidator;

class InvoiceTest extends AbstractTestCase
{
    /**
     * Tests the setData method with valid data
     *
     * @return void
     */
    public function testSetDataWithValidData()
    {
        $validator = new InvoiceValidator;
        $invoice = new Invoice;
        $invoice->setData($this->getValidInvoiceData(), $validator);
        $this->assertEquals($this->getValidInvoiceData(), $invoice->getData());
    }

    /**
     * Tests the setData method with invalid data
     *
     * @return void
     * @expectedException \Serato\InvoiceQueue\Exception\ValidationException
     */
    public function testSetDataWithInvalidData()
    {
        $validator = new InvoiceValidator;
        $invoice = new Invoice;
        $invoice->setData($this->getInvalidInvoiceData(), $validator);
    }

    /**
     * Tests the setItem
     *
     * @return void
     */
    public function testSetItemMethod()
    {
        $item = [
            'sku' => 'SKU1',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => 'V'
        ];

        $invoice = new Invoice;

        $invoice->addItem(
            $item['sku'],
            $item['quantity'],
            $item['amount_gross'],
            $item['amount_tax'],
            $item['amount_net'],
            $item['unit_price'],
            $item['tax_code']
        );

        $data = $invoice->getData();

        $this->assertEquals($item, $data['items'][0]);
    }

    private function getValidInvoiceData()
    {
        return  [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                [
                    'sku' => 'SKU1',
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ]
            ]
        ];
    }

    private function getInvalidInvoiceData()
    {
        return  [
            # 'source' => 'SwsEc', # Missing required field
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => '0', # Wrong data type
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                [
                    # 'sku' => 'SKU1', # Missing required field
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ]
            ]
        ];
    }
}
