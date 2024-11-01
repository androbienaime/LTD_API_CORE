<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Core\Shop;
use App\Models\Core\Brand;
use App\Models\Core\Order;
use App\Models\Core\Coupon;
use App\Models\Core\Account;
use App\Models\Core\Address;
use App\Models\Core\Carrier;
use App\Models\Core\Product;
use App\Policies\RolePolicy;
use App\Models\Core\Category;
use App\Models\Core\Currency;
use App\Models\Core\Customer;
use App\Models\Core\Delivery;
use App\Models\Core\Merchant;
use App\Models\Core\Tracking;
use App\Models\Location\City;
use App\Models\Core\Attribute;
use App\Models\Location\State;
use App\Models\Core\Roles\Role;
use App\Models\Core\OrderStatus;
use App\Models\Location\Country;
use App\Policies\Core\ShopPolicy;
use App\Models\Core\PaymentMethod;
use App\Policies\Core\BrandPolicy;
use App\Policies\Core\OrderPolicy;
use App\Policies\Core\CouponPolicy;
use App\Policies\Core\AccountPolicy;
use App\Policies\Core\AddressPolicy;
use App\Policies\Core\CarrierPolicy;
use App\Policies\Core\ProductPolicy;
use Illuminate\Support\Facades\Auth;
use App\Policies\Core\CategoryPolicy;
use App\Policies\Core\CurrencyPolicy;
use App\Policies\Core\CustomerPolicy;
use App\Policies\Core\DeliveryPolicy;
use App\Policies\Core\MerchantPolicy;
use App\Policies\Core\TrackingPolicy;
use App\Policies\Location\CityPolicy;
use App\Policies\Core\AttributePolicy;
use App\Policies\Location\StatePolicy;
use App\Policies\Core\OrderStatusPolicy;
use App\Policies\Location\CountryPolicy;
use App\Policies\Core\PaymentMethodPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Account::class => AccountPolicy::class,
        Carrier::class => CarrierPolicy::class,
        OrderStatus::class => OrderStatusPolicy::class,
        Currency::class => CurrencyPolicy::class,
        Customer::class => CustomerPolicy::class,
        Delivery::class => DeliveryPolicy::class,
        Merchant::class => MerchantPolicy::class,
        Order::class => OrderPolicy::class,
        PaymentMethod::class => PaymentMethodPolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Coupon::class => CouponPolicy::class,
        Attribute::class => AttributePolicy::class,
        Brand::class => BrandPolicy::class,
        Shop::class => ShopPolicy::class,
        Tracking::class => TrackingPolicy::class,
        Role::class => RolePolicy::class,
        City::class => CityPolicy::class,
        Country::class => CountryPolicy::class,
        State::class => StatePolicy::class,
        Address::class => AddressPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
