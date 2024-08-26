<?php

namespace App\Models\Admin;

use App\Models\Customer;
use App\Models\Admin\Product;
use App\Models\Admin\MerchantShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    public function merchantShops() : HasMany{
        return $this->hasMany(MerchantShop::class);
    }

    public function subscribers(){
        return $this->belongsToMany(Customer::class, 'subscriptions');
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}
