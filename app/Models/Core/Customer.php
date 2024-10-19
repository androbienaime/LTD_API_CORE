<?php

namespace App\Models\Core;

use App\Core\Trait\CustomerTrait;
use App\Models\Core\Order;
use App\Models\Core\Product;
use App\Models\Core\AddressCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model
{
    use HasFactory, Notifiable, InteractsWithMedia;

    protected $fillable = [
        "firstname",
        "lastname",
        "middle_name",
        "gender",
        "identityNumber_id",
        "email",
        "date_of_birth"
    ];

    public function getFullnameAttribute(){
        return "{$this->firstname} {$this->lastname}";
    }

    public function getFullCustomerAddressAttribute(){
        $address = $this->addressCustomers?->first()->address;
        return $address->getFullAddressAttribute();
    }
    public function order(){
        return $this->belongsToMany(Order::class);
    }

    public function addressCustomers() : HasMany{
        return $this->hasMany(AddressCustomer::class);
    }

    public function subscribtions(){
        //
    }

    public function likeProduct(){
        return $this->belongsToMany(Product::class);
    }

    public function commentProduct(){
        return $this->hasMany(Product::class);
    }

    public function shareProduct(){
        return $this->belongsToMany(Product::class); 
    }
}
