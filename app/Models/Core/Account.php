<?php

namespace App\Models\Core;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Account extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

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
    ];

    protected $casts = [
        'is_login' => 'boolean',
        'is_active' => 'boolean'
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

}
