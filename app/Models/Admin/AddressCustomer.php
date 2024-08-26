<?php

namespace App\Models\Admin;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AddressCustomer extends Pivot
{
    use HasFactory;

    public function address() : BelongsTo{
        return $this->belongsTo(Address::class);
    }

    public function customer() : BelongsTo{
        return $this->belongsTo(Customer::class);
    }
}
