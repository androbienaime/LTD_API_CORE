<?php

namespace App\Providers;

use App\Models\Core\Attribute;
use App\Models\Core\Brand;
use App\Models\Core\Category;
use App\Models\Core\Customer;
use App\Models\Core\Order;
use App\Models\Core\Product;
use App\Models\Core\Roles\Role;
use App\Observers\AttributeObserver;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\RoleObserver;
use App\Observers\CustomerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public static function boot() : void
    {
        Customer::observe(CustomerObserver::class);
        Role::observe(RoleObserver::class);
        Attribute::observe(AttributeObserver::class);
        Brand::observe(BrandObserver::class);
        Category::observe(CategoryObserver::class);
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
    }
}
