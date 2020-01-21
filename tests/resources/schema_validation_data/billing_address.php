<?php
return [
    [
        'billing_address',
        false,
        []
    ],
    [
        'billing_address',
        false,
        [
            'company_name' => 'Company Inc',
            'person_name' => 'Jo Bloggs',
            'address_1' => '123 Street Road',
            'address_2' => 'Suburbia',
            'address_3' => 'The Stixx',
            'city' => 'Townsville',
            'region' => 'Statey',
            'post_code' => '90210',
            'country_iso' => 'AUS' # Invalid. Must be 2 chars.
        ]
    ],
    [
        'billing_address',
        false,
        [
            'company_name' => 'Company Inc',
            'person_name' => 'Jo Bloggs',
            # 'address_1' => '123 Street Road', # Invalid. Is required.
            'address_2' => 'Suburbia',
            'address_3' => 'The Stixx',
            'city' => 'Townsville',
            'region' => 'Statey',
            'post_code' => '90210',
            'country_iso' => 'NZ'
        ]
    ],
    [
        'billing_address',
        false,
        [
            'company_name' => 'Company Inc',
            'person_name' => 'Jo Bloggs',
            'address_1' => '123 Street Road',
            'address_2' => 'Suburbia',
            'address_3' => 'The Stixx',
            # 'city' => 'Townsville', # Invalid. Is required.
            'region' => 'Statey',
            'post_code' => '90210',
            'country_iso' => 'NZ'
        ]
    ],
    [
        'billing_address',
        false,
        [
            'company_name' => 'Company Inc',
            'person_name' => 'Jo Bloggs',
            'address_1' => '123 Street Road',
            'address_2' => 'Suburbia',
            'address_3' => 'The Stixx',
            'city' => 'Townsville',
            'region' => 'Statey',
            'post_code' => '90210',
            # 'country_iso' => 'NZ' # Invalid. Is required.
        ]
    ],
    [
        'billing_address',
        true,
        [
            'company_name' => 'Company Inc',
            'person_name' => 'Jo Bloggs',
            'address_1' => '123 Street Road',
            'address_2' => 'Suburbia',
            'address_3' => 'The Stixx',
            'city' => 'Townsville',
            'region' => 'Statey',
            'post_code' => '90210',
            'country_iso' => 'NZ'
        ]
    ]
];
