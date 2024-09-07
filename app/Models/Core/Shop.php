<?php

namespace App\Models\Core;

use App\Models\Core\Customer;
use App\Models\Core\Product;
use App\Models\Core\MerchantShop;
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
