<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\Invoice;
use Serato\InvoiceQueue\InvoiceItem;
use Serato\InvoiceQueue\InvoiceValidator;
use Exception;

class InvoiceTest extends AbstractTestCase
{
    /**
     * Tests the Load method with valid array data
     *
     * @return void
     */
    public function testLoadWithValidArrayData()
    {
        $validator = new InvoiceValidator;
        $invoice = Invoice::load($this->getValidInvoiceData(), $validator);
        $this->assertEquals($this->getValidInvoiceData(), $invoice->getData());
        $this->assertEquals(1, count($invoice->getItems()));
    }

    /**
     * Tests the Load method with invalid array data
     *
     * @return void
     * @expectedException \Serato\InvoiceQueue\Exception\ValidationException
     */
    public function testLoadWithInvalidArrayData()
    {
        $validator = new InvoiceValidator;
        $invoice = Invoice::load($this->getInvalidInvoiceData(), $validator);
    }

    /**
     * Tests the Load method with valid string data
     *
     * @return void
     */
    public function testLoadWithValidStringData()
    {
        $json = json_encode($this->getValidInvoiceData());
        if ($json === false) {
            # This won't happen. The check is only here to phpstan happy :-)
            throw new Exception("Can't JSON encode array");
        }

        $validator = new InvoiceValidator;
        $invoice = Invoice::load($json, $validator);
        $this->assertEquals($this->getValidInvoiceData(), $invoice->getData());
        $this->assertEquals(1, count($invoice->getItems()));
    }

    /**
     * Tests the Load method with invalid string data
     *
     * @return void
     * @expectedException \Serato\InvoiceQueue\Exception\ValidationException
     */
    public function testLoadWithInvalidStringData()
    {
        $json = json_encode($this->getInvalidInvoiceData());
        if ($json === false) {
            # This won't happen. The check is only here to phpstan happy :-)
            throw new Exception("Can't JSON encode array");
        }

        $validator = new InvoiceValidator;
        $invoice = Invoice::load($json, $validator);
    }

    /**
     * Tests the addItem
     *
     * @return void
     */
    public function testSetItemMethod()
    {
        $item = InvoiceItem::create();
        $item
            ->setSku('SKU1')
            ->setQuantity(1)
            ->setAmountGross(200)
            ->setAmountTax(0)
            ->setAmountNet(100)
            ->setUnitPrice(100)
            ->setTaxCode('V');

        $invoice = Invoice::create();

        $invoice->addItem($item);
        $this->assertEquals(1, count($invoice->getItems()));
        $this->assertEquals($item, $invoice->getItems()[0]);

        $invoice->addItem($item);
        $this->assertEquals(2, count($invoice->getItems()));
        $this->assertEquals($item, $invoice->getItems()[1]);

        $data = $invoice->getData();

        $this->assertEquals(2, count($data['items']));
    }

    private function getValidInvoiceData()
    {
        return  [
            'source' => Invoice::SOURCE_SWSEC,
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'order_id' => 'ORDER--ID',
            'transaction_reference' => 'A STRING VAL',
            'payment_gateway' => Invoice::PAYMENTGATEWAY_BRAINTREE,
            'payment_instrument' => Invoice::PAYMENTINSTRUMENT_CREDITCARD,
            'moneyworks_debtor_code' => Invoice::MONEYWORKSDEBTORCODE_WEBC001,
            'subscription_id' => 'A STRING VAL',
            'currency' => Invoice::CURRENCY_USD,
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
            # 'source' => Invoice::SOURCE_SWSEC, # Missing required field
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'order_id' => 'ORDER--ID',
            'transaction_reference' => 'A STRING VAL',
            'payment_gateway' => Invoice::PAYMENTGATEWAY_BRAINTREE,
            'payment_instrument' => Invoice::PAYMENTINSTRUMENT_CREDITCARD,
            'moneyworks_debtor_code' => Invoice::MONEYWORKSDEBTORCODE_WEBC001,
            'subscription_id' => 'A STRING VAL',
            'currency' => Invoice::CURRENCY_USD,
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
