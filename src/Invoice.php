<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

use Serato\InvoiceQueue\Error\InvalidMethodNameError;
use ArgumentCountError;
use TypeError;
use DateTime;
use Exception;

/**
 * ** Invoice **
 *
 * A model for holding invoice data.
 *
 * @method string getSource()
 * @method string getInvoiceId()
 * @method string getInvoiceDate()
 * @method string getTransactionReference()
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

    # Note: this is only public so that we use it in unit tests :-)
    public const DATA_KEYS = [
        'source',
        'invoice_id',
        'invoice_date',
        'transaction_reference',
        'moneyworks_debtor_code',
        'subscription_id',
        'currency',
        'gross_amount',
        'billing_address_company_name',
        'billing_address_person_name',
        'billing_address_1',
        'billing_address_2',
        'billing_address_3',
        'billing_address_city',
        'billing_address_region',
        'billing_address_post_code',
        'billing_address_country_iso'
    ];

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
        if (count($args) > 0) {
            throw new ArgumentCountError(
                '`' . __CLASS__ . '::' . $methodName . '` expects 0 arguments. ' . count($args) . ' found.'
            );
        }

        $dataPropName = $this->getDataPropertyName(ltrim($methodName, 'get'), 'get');
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
        if (count($args) !== 1) {
            throw new ArgumentCountError(
                '`' . __CLASS__ . '::' . $methodName . '` expects 1 argument. ' . count($args) . ' found.'
            );
        }

        $val = $args[0];
        $isBillingAddressProp = false;

        $dataPropName = $this->getDataPropertyName(ltrim($methodName, 'set'), 'set');
        if (strpos($dataPropName, 'billing_address_') === 0) {
            $dataPropName = $this->getBillingAddressDataPropertyName($dataPropName);
            $isBillingAddressProp = true;
        }

        # Type check $val
        # `gross_amount` should be int, everything else should be a string
        if ($dataPropName === 'gross_amount') {
            if (gettype($val) !== 'integer') {
                throw new TypeError(
                    'Invalid type for `' . __CLASS__ . '::' . $methodName . '`, argument 0. Expects integer, ' .
                    gettype($val) . ' found.'
                );
            }
        } else {
            if (gettype($val) !== 'string') {
                throw new TypeError(
                    'Invalid type for `' . __CLASS__ . '::' . $methodName . '`, argument 0. Expects string, ' .
                    gettype($val) . ' found.'
                );
            }
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
        if (!in_array($dataPropertyName, self::DATA_KEYS)) {
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
