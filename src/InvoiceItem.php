<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

/**
 * ** Invoice **
 *
 * A model for working with invoice items.
 *
 * @method string getSku()
 * @method string getQuantity()
 * @method string getAmountGross()
 * @method string getAmountTax()
 * @method string getAmountNet()
 * @method string getUnitPrice()
 * @method string getTaxCode()
 *
 * @method self setSku(string $sku)
 * @method self setQuantity(int $qty)
 * @method self setAmountGross(int $amt)
 * @method self setAmountTax(int $amt)
 * @method self setAmountNet(int $amt)
 * @method self setUnitPrice(int $amt)
 * @method self setTaxCode(string $taxCode)
 */
class InvoiceItem extends AbstractDataContainer
{
    /**
     * {@inheritDoc}
     */
    protected static function getDataKeys(): array
    {
        return [
            # Property name    Data type
            'sku'           => 'string',
            'quantity'      => 'integer',
            'amount_gross'  => 'integer',
            'amount_tax'    => 'integer',
            'amount_net'    => 'integer',
            'unit_price'    => 'integer',
            'tax_code'      => 'string'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function getSchemaDefinition(): ?string
    {
        return 'line_item';
    }

    /**
     * {@inheritDoc}
     */
    protected static function getBaseData(): array
    {
        return [];
    }
}
