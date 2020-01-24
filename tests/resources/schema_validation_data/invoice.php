<?php
return [
    [
        null,
        false,
        []
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            'billing_address' => [], # Invalid
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => 0,
            # 'billing_address' => [], # Invalid. Is required.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
            # 'items' => [] # Invalid. Is required.
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
            'items' => [] # Invalid. Minimum length is 1
        ]
    ],
    [
        null,
        false,
        [
            # 'source' => 'SwsEc', # Invalid. Is required.
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            # 'invoice_id' => 'A STRING VAL', # Invalid. Required.
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            # 'invoice_date' => '2020-01-21T08:54:09Z', # Invalid. Is required.
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21 08:54:09', # Invalid format
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            # 'transaction_reference' => 'A STRING VAL', # Invalid. Is required.
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            # 'payment_provider' => 'BT', # Invalid. Is required.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BP', # Invalid. Value is not in enum.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            # 'moneyworks_debtor_code' => 'WEBC001', # Invalid. Is required.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'A VAL', # Invalid. Value is not in enum.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            # 'currency' => 'USD', # Invalid. Is required.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'UKP', # Invalid. Value not in enum.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            # 'gross_amount' => 0, # Invalid. Is required.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
            'moneyworks_debtor_code' => 'WEBC001',
            'subscription_id' => 'A STRING VAL',
            'currency' => 'USD',
            'gross_amount' => "0", # Invalid type.
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
        ]
    ],
    [
        null,
        false,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        true,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ],
    [
        null,
        true,
        [
            'source' => 'SwsEc',
            'invoice_id' => 'A STRING VAL',
            'invoice_date' => '2020-01-21T08:54:09Z',
            'transaction_reference' => 'A STRING VAL',
            'payment_provider' => 'BT',
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
        ]
    ]
];
