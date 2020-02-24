<?php
use Serato\InvoiceQueue\Invoice;

return [
    [
        'line_item',
        false,
        []
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 0, # Invalid. Must be > 0.
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => 'Q' # Invalid. Must be Invoice::TAXCODE_V or Invoice::TAXCODE_Z.
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => "0", # Invalid type
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            # 'sku' => 'ANY_STRING', # Invalid. Is required.
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            # 'quantity' => 1, # Invalid. Is required.
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            # 'amount_gross' => 0, # Invalid. Is required.
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            # 'amount_tax' => 0, # Invalid. Is required.
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            # 'amount_net' => 0, # Invalid. Is required.
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            # 'unit_price' => 0, # Invalid. Is required.
            'tax_code' => Invoice::TAXCODE_V
        ]
    ],
    [
        'line_item',
        false,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            # 'tax_code' => Invoice::TAXCODE_V # Invalid. Is required.
        ]
    ],
    [
        'line_item',
        true,
        [
            'sku' => 'ANY_STRING',
            'quantity' => 1,
            'amount_gross' => 0,
            'amount_tax' => 0,
            'amount_net' => 0,
            'unit_price' => 0,
            'tax_code' => Invoice::TAXCODE_V
        ]
    ]
];
