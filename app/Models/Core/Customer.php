<?php

namespace App\Models\Core;

use App\Core\Trait\Models\AccountGlobalScopeTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model
{
    use HasFactory, Notifiable, InteractsWithMedia, AccountGlobalScopeTrait;

    protected $fillable = [
        "firstname",
        "lastname",
        "middle_name",
        "gender",
        "identityNumber_id",
        "email",
        "date_of_birth",
        "shop_id",
        "account_id"
    ];

    protected static string $tableName = "customers";

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accounts() : BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
    public function getFullnameAttribute(){
        return "{$this->firstname} {$this->lastname}";
    }

    public function getFullCustomerAddressAttribute(){
        $address = $this->addressCustomers?->first()?->address;
        return $address?->getFullAddressAttribute();
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
