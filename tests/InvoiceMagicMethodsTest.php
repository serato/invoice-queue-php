<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\Invoice;
use Serato\InvoiceQueue\InvoiceValidator;

/**
 * Tests the magic methods implemented in Serato\InvoiceQueue\Invoice
 *
 * Note: this class is NOT analysed by phpstan because it purposefully tests
 * methods that don't exist and or uses method signatures incorrectly.
 * phpstan, by design, will fail these method calls.
 *
 * Any unit tests for Serato\InvoiceQueue\Invoice that SHOULD be anyalsed by
 * phpstan should be created in the Serato\InvoiceQueue\Test\InvoiceTest
 * test case.
 */
class InvoiceMagicMethodsTest extends AbstractTestCase
{
    /**
     * Tests the magic get and set methods
     *
     * @param string $propName
     * @param string $dataType
     * @return void
     *
     * @dataProvider invoiceDataPropsProvider
     */
    public function testMagicGetSetMethods(string $propName, string $dataType)
    {
        $val = 'StringVal';
        if ($dataType === 'integer') {
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
        foreach (Invoice::getDataKeys() as $key => $dataType) {
            $data[] = [$key, $dataType];
        }
        return $data;
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidMethodName()
    {
        $invoice = new Invoice;
        $invoice->noSuchMethod();
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
