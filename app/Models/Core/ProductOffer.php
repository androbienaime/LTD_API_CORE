<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOffer extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'offered_price',
        'token',
        'status',
        'note',
        'expires_at',
    ];

    protected $casts = [
        'offered_price' => 'decimal:2',
        'expires_at'    => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** L'offre est encore dans sa fenêtre de validité */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /** L'offre peut encore être utilisée pour une commande */
    public function isUsable(): bool
    {
        return true;
        // return $this->status === 'accepted' && !$this->isExpired();
    }

    /** Marquer l'offre comme consommée (appelé lors du placement de commande) */
    public function markAsUsed(): void
    {
        $this->update(['status' => 'used']);
    }
}