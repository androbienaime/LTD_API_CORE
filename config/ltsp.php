<?php
    return [
        "excludedResources" => [
            "account" => [
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
            ]    // Ajoutez d'autres ressources à exclure
        ],
    ];

?>