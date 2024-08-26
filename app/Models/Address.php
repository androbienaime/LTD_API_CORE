<?php

namespace App\Models;

use App\Models\Admin\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        "state",
        'city',
        "city_2",
        "phone",
        "phone_mobile",
        "email",
        "phonecode",
        "address1",
        "address2",
        "alias",
        "company",
        "active"
    ];

    public function addressShops() : HasMany{
        return $this->hasMany(Shop::class);
    }
}
