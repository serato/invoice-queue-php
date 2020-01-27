# Invoice Queue PHP SDK

A PHP library for interacting with an SQS message queue that holds invoice data.

## Adding to a project via composer.json

To include this library in a PHP project add the following line to the project's
`composer.json` file in the `require` section:

```json
{
  "require": {
    "serato/invoice-queue": "dev-master"
  }
}
```
See [Packagist](https://packagist.org/packages/serato/invoice-queue-php) for a list of all
available versions.

## Requirements

This library requires PHP 7.1 or greater.

## Style guide

Please ensure code adheres to the [PHP-FIG PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/)

Use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer/wiki) to validate your code against
coding standards:

```bash
$ ./vendor/bin/phpcs
```

## PHPStan

Use PHPStan for static code analysis:

```bash
$ vendor/bin/phpstan analyse
```

## Unit tests

Configuration for PHPUnit is defined within [phpunit.xml](phpunit.xml).

To run tests:

```bash
$ php vendor/bin/phpunit
```

## Usage

### Invoice Validator

The `Serato\InvoiceQueue\InvoiceValidator` class can validate a string or array against a
[JSON schema](./resources/invoice_schema.json) representing valid invoice data.

The `InvoiceValidator::validateJsonString` and `InvoiceValidator::validateArray` methods return a boolean value
indicating the success or otherwise of the validation. Use the The `InvoiceValidator::getErrors` method to
iterate over an array of validation errors.

The `InvoiceValidator::validateJsonString` and `InvoiceValidator::validateArray` methods can optionally take a
`$defintion` parameter which will validate the input against an named definition with the JSON schema document.
If not provided, the input is validated against the root element of the JSON schema.

```php
use Serato\InvoiceQueue\InvoiceValidator;

$validator = new Serato\InvoiceQueue\InvoiceValidator;

# Validate an array against the root schema.
if ($validator->validateArray(['my' => 'data'])) {
  // Data conforms to schema
} else {
  // Data does not conform to schema
  foreach ($validator->getErrors() as $error) {
    print_r($error);
  }
}

# Validate a string against an named definition within the JSON schema
if ($validator->validateJsonString('{"my":"data"}', 'line_item')) {
  // Data conforms to schema
} else {
  // Data does not conform to schema
  foreach ($validator->getErrors('line_item') as $error) {
    print_r($error);
  }
}
```

### Invoice model

The `Serato\InvoiceQueue\Invoice` class is a model for working with invoice data.

It provides getter and setter methods for individual invoice properties, a method for adding line items,
a means of populating the entire model from an array, and a means of extracting a data array from the model
that conforms to the [invoice JSON schema](./resources/invoice_schema.json).

```php
use Serato\InvoiceQueue\Invoice;

# Constructor is private.
# Always create using Invoice::create() static method
$invoice = Invoice::create();

# Set individual properties
$invoice
  ->setInvoiceId('MyInvoiceId')
  ->setCurrency('EUR')
  ->setBillingAddressCompanyName('Acme Inc');
// ...etc

# Get individual properties
echo $invoice->getInvoiceId();
echo $invoice->getCurrency();
echo $invoice->getBillingAddressCompanyName();
// ...etc

# Create an Invoice Item
use Serato\InvoiceQueue\InvoiceItem;

$item = InvoiceItem::create();
$item
  ->setSku('MySkuCode')
  ->setQuantity(1)
  ->setAmountGross(2000)
  ->setAmountTax(0)
  ->setAmountNet(1000)
  ->setUnitPrice(1000)
  ->setTaxCode('V');

# Add the Item to an Invoice
$invoice->addItem($item);

# Gets all items in invoice (returns an array of InvoiceItem objects)
$invoice->getItems();

# Get complete invoice data that conforms to JSON schema
$data = $invoice->getData();

# Use the `Invoice::load` static method to populate model with data
# (the data will be validated against the JSON schema)
$invoice = Invoice::load($data);

# If loading multiple invoices, create a single InvoiceValidator
# instance and pass it to `Invoice::load` for better performance.
$validator = new Serato\InvoiceQueue\InvoiceValidator;
$invoice1 = Invoice::load($data1, $validator);
$invoice2 = Invoice::load($data2, $validator);

```

### Sqs Client

The `Serato\InvoiceQueue\SqsQueue` provides functionality for interacting with AWS SQS message queues that
contain invoice data.

`SqsQueue` can return a queue URL, send `Serato\InvoiceQueue\Invoice` instances to a queue either individually
or in batches, and create queues if they don't currently exist.

```php
use Aws\Sdk;
use Serato\InvoiceQueue\SqsQueue;
use Monolog\Logger;

# Create AWS SQS client instance
$awsSdk = new Sdk();
$awsSqsClient->createSqs();

# Create a PSR LogInterface instance
$logger = new Logger('sqsqueue-log');

# Constructor requires:
# - An AWS SQS client
# - Environment string (one of 'test' or 'production')
# - PSR LogInterface
# - Host application name (for logging purposes)
$queue = new SqsQueue($awsSqsClient, 'test', $logger, 'My App');

# Get the queue name or URL of the underlying SQS queue
$queue->getQueueUrl();
$queue->getQueueName();

# Send an individual Invoice instance to the queue
# Invoice data will be validated against the JSON schema
$invoice = Invoice::create();
// ... populate $invoice
$messageId = $queue->sendInvoice($invoice);

# Send multiple invoices as a batch
# Invoice data will be validated against the JSON schema
# Batch will sent when interal batch size limit is reached or when
# SqsQueue instance is destroyed
$invoice1 = Invoice::create();
// ... populate $invoice1
$invoice2 = Invoice::create();
// ... populate $invoice2

$queue
  ->sendInvoiceToBatch($invoice1)
  ->sendInvoiceToBatch($invoice2);
```
