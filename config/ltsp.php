<?php
    return [
        "excludedResources" => [
            "account" => [
                'AccountResource',
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
    ];

?>