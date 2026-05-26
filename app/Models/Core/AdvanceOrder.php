<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount_order_advance',   // montant de cette avance
        'currency_id',
        'payment_method_id',         // 'cash' | 'card'
        "account_id",
    ];

    protected $casts = [
        'amount_order_advance' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
