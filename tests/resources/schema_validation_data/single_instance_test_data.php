<?php
use Serato\InvoiceQueue\Invoice;

return [
    [
        null,
        true,
        [
            'source' => Invoice::SOURCE_SWSEC,
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'order_id' => 'ORDER--ID',
            'transaction_reference' => 'A STRING VAL',
            'payment_gateway' => 'braintree',
            'payment_instrument' => 'paypal_account',
            'payment_instrument_transaction_reference' => 'any ol thang',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                [
                    'sku' => 'SKU1',
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ]
            ]
        ],
        'Data 1: valid'
    ],
    [
        null,
        false,
        [
            'source' => Invoice::SOURCE_SWSEC,
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'order_id' => 'ORDER--ID',
            'transaction_reference' => 'A STRING VAL',
            'payment_gateway' => 'braintree',
            'payment_instrument' => 'paypal_account',
            'payment_instrument_transaction_reference' => 'any ol thang',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                # Valid item
                [
                    'sku' => 'SKU1',
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ],
                # Invalid item
                []
            ]
        ],
        'Data 2: invalid'
    ],
    [
        null,
        true,
        [
            'source' => Invoice::SOURCE_SWSEC,
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'order_id' => 'ORDER--ID',
            'transaction_reference' => 'A STRING VAL',
            'payment_gateway' => 'braintree',
            'payment_instrument' => 'creditcard',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [
                'company_name' => 'Company Inc',
                'person_name' => 'Jo Bloggs',
                'address_1' => '123 Street Road',
                'address_2' => 'Suburbia',
                'address_3' => 'The Stixx',
                'city' => 'Townsville',
                'region' => 'Statey',
                'post_code' => '90210',
                'country_iso' => 'NZ'
            ],
            'items' => [
                [
                    'sku' => 'SKU1',
                    'quantity' => 1,
                    'amount_gross' => 0,
                    'amount_tax' => 0,
                    'amount_net' => 0,
                    'unit_price' => 0,
                    'tax_code' => 'V'
                ],
                [
                    'sku' => 'SKU2',
                    'quantity' => 20,
                    'amount_gross' => 21000,
                    'amount_tax' => 1000,
                    'amount_net' => 20000,
                    'unit_price' => 1000,
                    'tax_code' => 'V'
                ]
            ]
        ],
        'Data 3: valid'
    ]
];
