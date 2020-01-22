<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\Invoice;

class InvoiceTest extends AbstractTestCase
{
    /**
     * Tests the magic get and set methods
     *
     * @param string $propName
     * @return void
     *
     * @dataProvider invoiceDataPropsProvider
     */
    public function testMagicGetSetMethods(string $propName)
    {
        $val = 'StringVal';
        if ($propName === 'gross_amount') {
            $val = 0;
        }
        $baseMethodName = str_replace(' ', '', ucwords(str_replace('_', ' ', $propName)));
        $setMethodName = 'set' . $baseMethodName;
        $getMethodName = 'get' . $baseMethodName;

        $invoice = new Invoice;
        $invoice->$setMethodName($val);
        $this->assertEquals($val, $invoice->$getMethodName());
    }

    public function invoiceDataPropsProvider(): array
    {
        $data = [];
        foreach (Invoice::DATA_KEYS as $key) {
            $data[] = [$key];
        }
        return $data;
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidGetMethodName()
    {
        $invoice = new Invoice;
        $invoice->getNoSuchMethod();
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidSetMethodName()
    {
        $invoice = new Invoice;
        $invoice->setNoSuchMethod('val');
    }

    /**
     * Provide an argument to set method (set methods expect 0 args)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidGetMethodArgs()
    {
        $invoice = new Invoice;
        $invoice->getSource('val');
    }

    /**
     * Provide no arguments to get method (get methods expect 1 arg)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidSetMethodArgs1()
    {
        $invoice = new Invoice;
        $invoice->setSource();
    }

    /**
     * Provide 2 arguments to get method (get methods expect 1 arg)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidSetMethodArgs2()
    {
        $invoice = new Invoice;
        $invoice->setSource('val', 'val');
    }

    /**
     * Pass an argument of incorrect type to set method
     *
     * @expectedException \TypeError
     */
    public function testInvalidSetMethodArgType()
    {
        $invoice = new Invoice;
        $invoice->setSource(0);
    }

    /**
     * Test that getting an unset value returns NULL.
     */
    public function testGetMethodNullDefaultValue()
    {
        $invoice = new Invoice;
        $this->assertEquals(null, $invoice->getSource());
    }
}
