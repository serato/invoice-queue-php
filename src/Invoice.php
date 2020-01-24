<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

use Serato\InvoiceQueue\InvoiceValidator;
use Serato\InvoiceQueue\Error\InvalidMethodNameError;
use Serato\InvoiceQueue\Exception\ValidationException;
use ArgumentCountError;
use TypeError;
use DateTime;
use Exception;

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
class Invoice
{
    /** @var array */
    private $data = [
        'billing_address' => [],
        'items' => []
    ];

    /**
     * Constructs the object.
     *
     * Optionally takes a array of data, $data, and InvoiceValidator instance with which to populate
     * the object.
     *
     * If $data is provided an InvoiceValidator instance must also be provided.
     *
     * @param array|null $data
     * @param InvoiceValidator|null $validator
     * @throws ValidationException
     * @throws ArgumentCountError
     */
    public function __construct(?array $data = null, ?InvoiceValidator $validator = null)
    {
        if ($data !== null) {
            if ($validator === null) {
                throw new ArgumentCountError(
                    'You must provide a InvoiceValidator instance when setting the data argument'
                );
            }
            if ($validator->validateArray($data)) {
                $this->data = $data;
            } else {
                throw new ValidationException($validator->getErrors());
            }
        }
    }

    # Note: this is only public so that we use it in unit tests :-)
    public const DATA_KEYS = [
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

    /**
     * Returns an array structure containing complete invoice data.
     *
     * The array structure conforms to the JSON schema used by Serato\InvoiceQueue\InvoiceValidator.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Creates an instance from an array.
     *
     * @param array $data
     * @param InvoiceValidator $validator
     * @return self
     */
    public static function load(array $data, InvoiceValidator $validator): self
    {
        return new static($data, $validator);
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
     * @throws InvalidMethodNameError
     */
    public function __call(string $methodName, array $args)
    {
        if (strpos($methodName, 'get') === 0) {
            return $this->callGetMethod($methodName, $args);
        }
        if (strpos($methodName, 'set') === 0) {
            return $this->callSetMethod($methodName, $args);
        }
        throw new InvalidMethodNameError(
            'Invalid method name `' . __CLASS__ . '::' . $methodName . '`'
        );
    }

    /**
     * Implements a magic `get` method
     *
     * @param string $methodName
     * @param array $args
     * @return mixed
     *
     * @throws InvalidMethodNameError
     * @throws ArgumentCountError
     */
    private function callGetMethod(string $methodName, array $args)
    {
        $dataPropName = $this->getDataPropertyName(ltrim($methodName, 'get'), 'get');

        if (count($args) > 0) {
            throw new ArgumentCountError(
                '`' . __CLASS__ . '::' . $methodName . '` expects 0 arguments. ' . count($args) . ' found.'
            );
        }

        if (strpos($dataPropName, 'billing_address_') === 0) {
            $dataPropName = $this->getBillingAddressDataPropertyName($dataPropName);
            return isset($this->data['billing_address'][$dataPropName]) ?
                $this->data['billing_address'][$dataPropName] :
                null;
        } else {
            return isset($this->data[$dataPropName]) ? $this->data[$dataPropName] : null;
        }
    }

    /**
     * Implements a magic `get` method
     *
     * @param string $methodName
     * @param array $args
     * @return self
     *
     * @throws InvalidMethodNameError
     * @throws ArgumentCountError
     * @throws TypeError
     */
    private function callSetMethod(string $methodName, array $args): self
    {
        $dataPropName = $this->getDataPropertyName(ltrim($methodName, 'set'), 'set');

        if (count($args) !== 1) {
            throw new ArgumentCountError(
                '`' . __CLASS__ . '::' . $methodName . '` expects 1 argument. ' . count($args) . ' found.'
            );
        }

        $val = $args[0];
        $isBillingAddressProp = false;

        $dataType = self::DATA_KEYS[$dataPropName];

        if (strpos($dataPropName, 'billing_address_') === 0) {
            $dataPropName = $this->getBillingAddressDataPropertyName($dataPropName);
            $isBillingAddressProp = true;
        }

        if (gettype($val) !== $dataType) {
            throw new TypeError(
                'Invalid type for `' . __CLASS__ . '::' . $methodName . '`, argument 0. Expects ' . $dataType .
                ', ' . gettype($val) . ' found.'
            );
        }

        if ($isBillingAddressProp) {
            $this->data['billing_address'][$dataPropName] = $val;
        } else {
            $this->data[$dataPropName] = $val;
        }

        return $this;
    }

    /**
     * Maps a camel cased get or set method name to an internal snake cased data array key
     *
     * @param string $methodName
     * @return string
     *
     * @throws InvalidMethodNameError
     */
    private function getDataPropertyName(string $methodName, string $methodPrefix): string
    {
        $dataPropertyName = preg_replace_callback(
            '|([A-Z0-9])|',
            function ($matches) {
                return '_' . strtolower($matches[0]);
            },
            $methodName
        );
        if ($dataPropertyName === null) {
            # This should never happen :-)
            throw new Exception;
        }
        $dataPropertyName = ltrim($dataPropertyName, '_');
        if (!isset(self::DATA_KEYS[$dataPropertyName])) {
            throw new InvalidMethodNameError(
                'Invalid method name `' . __CLASS__ . '::' . $methodPrefix . $methodName . '`.'
            );
        }
        return $dataPropertyName;
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
