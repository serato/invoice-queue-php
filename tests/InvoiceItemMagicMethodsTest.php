<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use Serato\InvoiceQueue\Test\AbstractTestCase;
use Serato\InvoiceQueue\InvoiceItem;
use Serato\InvoiceQueue\InvoiceValidator;
use ReflectionMethod;

/**
 * Tests the magic methods implemented in Serato\InvoiceQueue\InvoiceItem
 *
 * Note: this class is NOT analysed by phpstan because it purposefully tests
 * methods that don't exist and or uses method signatures incorrectly.
 * phpstan, by design, will fail these method calls.
 *
 * Any unit tests for Serato\InvoiceQueue\Invoice that SHOULD be anyalsed by
 * phpstan should be created in the Serato\InvoiceQueue\Test\InvoiceItemTest
 * test case.
 */
class InvoiceItemMagicMethodsTest extends AbstractTestCase
{
    /**
     * Tests the magic get and set methods
     *
     * @param string $propName
     * @param string $dataType
     * @return void
     *
     * @dataProvider invoiceItemDataPropsProvider
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

        $invoiceItem = InvoiceItem::create();
        $invoiceItem->$setMethodName($val);
        $this->assertEquals($val, $invoiceItem->$getMethodName());
    }

    public function invoiceItemDataPropsProvider(): array
    {
        $getDataKeysMethod = new ReflectionMethod('\Serato\InvoiceQueue\InvoiceItem', 'getDataKeys');
        $getDataKeysMethod->setAccessible(true);

        $data = [];
        foreach ($getDataKeysMethod->invoke(null) as $key => $dataType) {
            $data[] = [$key, $dataType];
        }
        return $data;
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidMethodName()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->noSuchMethod();
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidGetMethodName()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->getNoSuchMethod();
    }

    /**
     * @expectedException \Serato\InvoiceQueue\Error\InvalidMethodNameError
     */
    public function testInvalidSetMethodName()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setNoSuchMethod('val');
    }

    /**
     * Provide an argument to set method (set methods expect 0 args)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidGetMethodArgs()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->getSku('val');
    }

    /**
     * Provide no arguments to get method (get methods expect 1 arg)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidSetMethodArgs1()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setSku();
    }

    /**
     * Provide 2 arguments to get method (get methods expect 1 arg)
     *
     * @expectedException \ArgumentCountError
     */
    public function testInvalidSetMethodArgs2()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setSku('val', 'val');
    }

    /**
     * Pass an argument of incorrect type to set method
     *
     * @expectedException \TypeError
     */
    public function testInvalidSetMethodArgType()
    {
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setSku(0);
    }

    /**
     * Test that getting an unset value returns NULL.
     */
    public function testGetMethodNullDefaultValue()
    {
        $invoiceItem = InvoiceItem::create();
        $this->assertEquals(null, $invoiceItem->getSku());
    }
}
