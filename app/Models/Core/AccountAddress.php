<?php

namespace App\Models\Core;

use App\Models\Core\Account;
use App\Models\Core\Address;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountAddress extends Pivot
{
    protected $fillable = [
        "account_id",
        "address_id",
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
