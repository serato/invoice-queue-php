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

The `InvoiceValidator::validateString` and `InvoiceValidator::validateArray` methods return a boolean value
indicating the success or otherwise of the validation. Use the The `InvoiceValidator::getErrors` method to
iteract over an array of specific validation errors.

The `InvoiceValidator::validateString` and `InvoiceValidator::validateArray` methods can optionally take a
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

if ($validator->validateString('{"my":"data"}', 'line_item')) {
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

$invoice = new Invoice;

# Set individual properties

$invoice
  ->setInvoiceId('MyInvoiceId')
  ->setCurrency('EUR')
  ->setBillingAddressCompanyName('Acme Inc');
# ...etc

# Get individual properties

echo $invoice->getInvoiceId();
echo $invoice->getCurrency();
echo $invoice->getBillingAddressCompanyName();
# ...etc

# Add a line item

$invoice->addItem('MySkuCode', 2, 2000, 0, 2000, 1000, 'Z');

# Get complete invoice data that conforms to JSON schema

$data = $invoice->getData();

# Use `Invoice::setData` to populate model with data (the data will be
# validated against the JSON schema)

$invoice = new Invoice;
$invoice->setData($data);

# `Invoice::setData` can optionally take a `Serato\InvoiceQueue\InvoiceValidator`
# instance. This can provide better performance if creating multiple Invoice
# instances because is saves on the overhead of having to create multiple
# InvoiceValidator instances.

$validator = new Serato\InvoiceQueue\InvoiceValidator;
$invoice1 = new Invoice;
$invoice1->setData($data1, $validator);
$invoice2 = new Invoice;
$invoice2->setData($data2, $validator);
```

### Sqs Client

The `Serato\InvoiceQueue\SqsQueue` provides functionality for interacting with AWS SQS message queues that
contain invoice data.

`SqsQueue` can return a queue URL, send `Serato\InvoiceQueue\Invoice` instances to a queue either individually
or in batches, and create queues if they don't currently exist.

```php
use Aws\Sdk;
use Serato\InvoiceQueue\SqsQueue;

# Create AWS SQS client instance

$awsSdk = new Sdk();
$awsSqsClient->createSqs();

# Constructor requires an AWS SQS client and an environment string
# (one of 'test' or 'production')

$queue = new SqsQueue($awsSqsClient, 'test');

# Get the queue name or URL of the underlying SQS queue

$queue->getQueueUrl();
$queue->getQueueName();

# Send an individual Invoice instance to the queue
# Invoice data will be validated against the JSON schema

$invoice = new Invoice;
$invoice->setData(['my' => 'data']);
$messageId = $queue->sendInvoice($invoice);

# Send multiple invoices as a batch
# Invoice data will be validated against the JSON schema
# Batch will sent when interal batch size limit is reached or when
# SqsQueue instance is destroyed

$invoice1 = new Invoice;
$invoice1->setData(['my' => 'data1']);
$invoice2 = new Invoice;
$invoice2->setData(['my' => 'data2']);

$queue
  ->sendInvoiceToBatch($invoice1)
  ->sendInvoiceToBatch($invoice2);
```
