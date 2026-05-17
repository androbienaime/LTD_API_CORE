<?php

namespace App\Models\Core;

use App\Events\AccountShopCreating;
use App\Events\AccountShopDeleting;
use App\Events\AccountShopUpdating;
use App\Models\Core\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class AccountShop extends Pivot
{
    use SoftDeletes;

    protected $table = 'account_shop';
    public $incrementing = true; // nécessaire pour les observers sur pivot

    protected $fillable = ['account_id', 'shop_id', 'role_id'];

    // Les events sont dispatchés automatiquement si on passe par le modèle
    protected $dispatchesEvents = [
        'creating' => AccountShopCreating::class,
        'updating' => AccountShopUpdating::class,
        'deleting' => AccountShopDeleting::class,
    ];

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'super-admin';
    }
}