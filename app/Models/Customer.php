<?php

namespace App\Models;

use App\Models\Admin\Order;
use App\Models\Admin\Product;
use App\Models\Admin\AddressCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, Notifiable;

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
