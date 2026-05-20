<?php
    return [
        "excludedResources" => [
            "account" => [
                // 'AccountResource',
                'UserResource',
                'AddressResource',
                'CityResource',
                'StateResource',
                'CountryResource',
                'DeliveryResource',
                'MerchantResource',
                'PaymentMethodResource',
                'OrderStatusResource',
                'TrackingResource',
                'CouponResource',
                'CarrierResource',
                'CurrencyResource',
            ],
            "role-account" =>[
                "manage-products"=>[
                    "AccountResource",
                    "RoleResource",
                    "ShopResource",
                ],
                "cashier"=>[
                    "AccountResource",
                    "RoleResource",
                    "ShopResource",
                    "ProductResource",
                    "BrandResource",
                    "CategoryResource",
                    "AttributeResource",
                ]
            ]
        ],

        "manage-products"=>true,
        "cashier"=>true,

        "currency" => [
            'api_url' => env('CURRENCY_API_URL'),
            'api_key' => env('CURRENCY_API_KEY'),
            'base_currency' => 'USD'
        ]
    ];

?>
