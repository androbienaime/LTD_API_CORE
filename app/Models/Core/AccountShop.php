<?php

namespace App\Models\Core;

use App\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountShop extends Pivot
{

    use SoftDeletes;

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function shop() : BelongsTo{
        return $this->belongsTo(Shop::class);
    }

}
