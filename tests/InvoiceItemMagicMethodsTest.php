<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Test;

use ArgumentCountError;
use Serato\InvoiceQueue\Error\InvalidMethodNameError;
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
     * @return void
     */
    public function testInvalidMethodName()
    {
        $this->expectException(InvalidMethodNameError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->noSuchMethod();
    }

    public function testInvalidGetMethodName()
    {
        $this->expectException(InvalidMethodNameError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->getNoSuchMethod();
    }

    public function testInvalidSetMethodName()
    {
        $this->expectException(InvalidMethodNameError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setNoSuchMethod('val');
    }

    /**
     * Provide an argument to set method (set methods expect 0 args)
     *
     *
     */
    public function testInvalidGetMethodArgs()
    {
        $this->expectException(ArgumentCountError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->getSku('val');
    }

    /**
     * Provide no arguments to get method (get methods expect 1 arg)
     *
     *
     */
    public function testInvalidSetMethodArgs1()
    {
        $this->expectException(ArgumentCountError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setSku();
    }

    /**
     * Provide 2 arguments to get method (get methods expect 1 arg)
     *
     *
     */
    public function testInvalidSetMethodArgs2()
    {
        $this->expectException(ArgumentCountError::class);
        $invoiceItem = InvoiceItem::create();
        $invoiceItem->setSku('val', 'val');
    }

    /**
     * Pass an argument of incorrect type to set method
     *
     *
     */
    public function testInvalidSetMethodArgType()
    {
        $this->expectException(\TypeError::class);
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
