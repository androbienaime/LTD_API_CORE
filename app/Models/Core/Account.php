<?php

namespace App\Models\Core;

use Filament\Panel;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Builder;
use Filament\Models\Contracts\FilamentUser;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Account extends Authenticatable implements HasMedia, FilamentUser, HasName, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasRoles, HasPanelShield;

    protected $guard = "accounts";

    protected $fillable = [
        "firstname",
        "lastname",
        "username",
        "email",
        "password",
        "loginBy",
        "phone",
        "otp",
        "otp_activated_at",
        "otp_expired_at",
        "last_login",
        "agent",
        "host",
        "tokens",
        "is_active",
        "is_verified",
        "is_login",
        "is_notification_active",
        "lang",
        "ip_address",
        "created_at",
        "updated_at",
        "type"
    ];

    protected $casts = [
        'is_login' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',

    ];

    protected $dates = [
        'otp_activated_at',
        'otp_expired_at',
        'last_login',
        'created_at',
        'updated_at',
        'date_of_birth'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_activated_at',
        'otp_expired_at',
        'agent',
        'host',
    ];

    public function getFilamentName(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function getAvatarAttribute()
    {
        return $this->getFirstMediaUrl('avatar');
    }

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function addresses()
    {
        return $this->hasMany(AccountAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }



    public static function generateUniqueUsername($name) {
        // Génère un username sans tirets
        $username = Str::slug($name, ''); // Remplacer les tirets par une chaîne vide

        // Recherche tous les usernames qui commencent par le slug généré
        $existingUsernames = Account::where('username', 'LIKE', "{$username}%")
                                ->pluck('username');

        // Si le username n'existe pas déjà, on le retourne directement
        if (!$existingUsernames->contains($username)) {
            return $username;
        }

        // Filtre les usernames qui ont un suffixe numérique et extrait les numéros
        $maxSuffix = $existingUsernames->filter(function ($value) use ($username) {
            return preg_match("/^{$username}(\d+)$/", $value);
        })->map(function ($value) use ($username) {
            return intval(str_replace($username, '', $value));
        })->max();

        // Incrémente le plus grand suffixe trouvé ou commence à 1
        $newUsername = $username . ($maxSuffix + 1);

        return $newUsername;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
        // TODO: Implement canAccessPanel() method.
    }

    public function shop() : BelongsToMany
    {
        return $this->belongsToMany(Shop::class);
    }

    public function shopActive()
    {
        return $this->belongsToMany(Shop::class)
            ->wherePivot('deleted_at', null)  // non softdelete
            ->wherePivot('status', 'active') // status actif dans la table pivot
            ->withPivot(['created_at', 'status'])
            ->orderBy('pivot_created_at', 'desc')
            ->take(1);  // prendre le dernier
    }
//    public function canAccessTenant(Model $tenant): bool
//    {
//        return $this->shop()->whereKey($tenant)->exists();
//    }
//
//    public function getTenants(Panel $panel): array|Collection
//    {
//        return $this->shop;
//    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
        // TODO: Implement getFilamentAvatarUrl() method.
    }
}
