<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

/**
 * ** Invoice **
 *
 * A model for working with invoice data.
 *
 * Data is added to, and retrieved from an instance via `set` and `get methods along
 * with the self::addItem method.
 *
 * An array of complete invoice data can be returned via the self::getData method.
 * This array conforms to the JSON schema used by Serato\InvoiceQueue\InvoiceValidator.
 *
 * The model can populated from an array using the Invoice::load static method.
 *
 * @method string getSource()
 * @method string getInvoiceId()
 * @method string getInvoiceDate()
 * @method string getOrderId()
 * @method string getTransactionReference()
 * @method string getPaymentProvider()
 * @method string getMoneyworksDebtorCode()
 * @method string getSubscriptionId()
 * @method string getCurrency()
 * @method int getGrossAmount()
 * @method string getBillingAddressCompanyName()
 * @method string getBillingAddressPersonName()
 * @method string getBillingAddress1()
 * @method string getBillingAddress2()
 * @method string getBillingAddress3()
 * @method string getBillingAddressCity()
 * @method string getBillingAddressRegion()
 * @method string getBillingAddressPostCode()
 * @method string getBillingAddressCountryIso()
 *
 * @method self setSource(string $source)
 * @method self setInvoiceId(string $invoiceId)
 * @method self setInvoiceDate(string $dateIso8601)
 * @method self setOrderId(string $orderId)
 * @method self setTransactionReference(string $ref)
 * @method self setPaymentProvider(string $ref)
 * @method self setMoneyworksDebtorCode(string $mwDebtorCode)
 * @method self setSubscriptionId(string $subId)
 * @method self setCurrency(string $currency)
 * @method self setGrossAmount(int $grossAmount)
 * @method self setBillingAddressCompanyName(string $companyName)
 * @method self setBillingAddressPersonName(string $name)
 * @method self setBillingAddress1(string $address1)
 * @method self setBillingAddress2(string $address2)
 * @method self setBillingAddress3(string $address3)
 * @method self setBillingAddressCity(string $city)
 * @method self setBillingAddressRegion(string $region)
 * @method self setBillingAddressPostCode(string $postCode)
 * @method self setBillingAddressCountryIso(string $countryIso)
 */
class Invoice extends AbstractDataContainer
{
    public static function getDataKeys(): array
    {
        return [
            # Property name                    Data type
            'source'                        => 'string',
            'invoice_id'                    => 'string',
            'invoice_date'                  => 'string',
            'order_id'                      => 'string',
            'transaction_reference'         => 'string',
            'payment_provider'              => 'string',
            'moneyworks_debtor_code'        => 'string',
            'subscription_id'               => 'string',
            'currency'                      => 'string',
            'gross_amount'                  => 'integer',
            'billing_address_company_name'  => 'string',
            'billing_address_person_name'   => 'string',
            'billing_address_1'             => 'string',
            'billing_address_2'             => 'string',
            'billing_address_3'             => 'string',
            'billing_address_city'          => 'string',
            'billing_address_region'        => 'string',
            'billing_address_post_code'     => 'string',
            'billing_address_country_iso'   => 'string'
        ];
    }

    /**
     * Adds a line item to the invoice
     *
     * @param string    $sku            SKU code of line item.
     * @param integer   $quantity       Quantity of unit items.
     * @param integer   $amountGross    Gross amount of the line item ((unit price + unit tax) * quantity),
     *                                  expressed in cents.
     * @param integer   $amountTax      Tax amount of the line item (unit tax * quantity), expressed in cents.
     * @param integer   $amountNet      Net amount of the line item (unit price * quantity), expressed in cents.
     * @param integer   $unitPrice      Unit price of the line item, expressed in cents.
     * @param string    $taxCode        Tax code for line item. 'V' when any rate of tax is added, 'Z' when no
     *                                  tax is added.
     * @return self
     */
    public function addItem(
        string $sku,
        int $quantity,
        int $amountGross,
        int $amountTax,
        int $amountNet,
        int $unitPrice,
        string $taxCode
    ): self {
        $item = [
            'sku' => $sku,
            'quantity' => $quantity,
            'amount_gross' => $amountGross,
            'amount_tax' => $amountTax,
            'amount_net' => $amountNet,
            'unit_price' => $unitPrice,
            'tax_code' => $taxCode
        ];
        $this->data['items'][] = $item;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataProp(string $dataPropName)
    {
        if (strpos($dataPropName, 'billing_address_') === 0) {
            $dataPropName = $this->getBillingAddressDataPropertyName($dataPropName);
            return isset($this->data['billing_address'][$dataPropName]) ?
                $this->data['billing_address'][$dataPropName] :
                null;
        } else {
            return parent::getDataProp($dataPropName);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function setDataProp(string $dataPropName, $val)
    {
        if (strpos($dataPropName, 'billing_address_') === 0) {
            $dataPropName = $this->getBillingAddressDataPropertyName($dataPropName);
            $this->data['billing_address'][$dataPropName] = $val;
            return $this;
        } else {
            return parent::setDataProp($dataPropName, $val);
        }
    }

    private function getBillingAddressDataPropertyName(string $name): string
    {
        $name = str_replace('billing_address_', '', $name);
        if (in_array($name, ['1', '2', '3'])) {
            $name = 'address_' . $name;
        }
        return $name;
    }
}
