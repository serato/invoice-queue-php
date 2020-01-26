<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

/**
 * ** Invoice **
 *
 * A model for working with invoices.
 *
 * Use the `self::addItem` method to add instances of `Serato\InvoiceQueue\InvoiceItem`
 * to the model.
 * 
 * Use the `self::getItems` method to return all `Serato\InvoiceQueue\InvoiceItem` instances.
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
    /** @var array */
    private $items = [];

    /**
     * {@inheritDoc}
     */
    protected static function getDataKeys(): array
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
     * {@inheritDoc}
     */
    protected static function getSchemaDefinition(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected static function getBaseData(): array
    {
        return [
            'billing_address' => [],
            'items' => []
        ];
    }

    /**
     * Adds an invoice item to the invoice
     *
     * @param InvoiceItem $item
     * @return self
     */
    public function addItem(InvoiceItem $item): self
    {
        $this->items[] = $item;
        $this->data['items'][] = $item->getData();
        return $this;
    }

    /**
     * Returns an array of InvoiceItem instances
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    protected function setData(array $data): void
    {
        parent::setData($data);
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->items[] = InvoiceItem::load($item, $this->validator);
            }
        }
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
