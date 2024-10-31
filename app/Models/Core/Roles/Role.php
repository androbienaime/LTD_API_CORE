<?php

namespace App\Models\Core\Roles;

use App\Models\User;
use App\Models\Core\Shop;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id'
    ];

    protected static function booted()
    {
                 // 1. Séparation de la logique de scope pour la lecture
        static::addGlobalScope('tenant_scope', function($query) {
            // Vérifier si c'est une requête SELECT en examinant le type de requête
            if ($query->getQuery()->columns !== null) {
                $query->where(function($q) {
                    if(auth()->guard('web')->check()){
                        if(!auth()->user()->hasRole('super_admin')){
                            $tenantId = auth()->guard('web')->user()->id;
                            $q->where('guard_name', 'web')
                            ->where(function($query) use ($tenantId) {
                                $query->where('tenant_id', $tenantId)
                                    ->orWhereNull('tenant_id');
                            });
                        }
                    }else if(auth()->guard('account')->check()){
                        $tenantId = auth()->guard('account')->user()?->shopActive->first()->id;
                        $q->where('guard_name', 'account')
                         ->where(function($query) use ($tenantId) {
                             $query->where('tenant_id', $tenantId)
                                   ->orWhereNull('tenant_id');
                         });
                    }
                });
            }
        });
    }

    public function getTenant()
    {
        if(auth()->guard('web')->check()) {
            if($this->guard_name == 'web') {
                return $this->belongsTo(User::class, 'tenant_id');
            } elseif($this->guard_name == 'account') {
                return $this->belongsTo(Shop::class, 'tenant_id');
            }
        } elseif(auth()->guard('account')->check()) {
            if($this->guard_name == 'account') {
                // On veut le dernier shop actif
                return $this->belongsTo(Shop::class, 'tenant_id');
            }
        }
    }

    public static function createUniqueRole($name){
        $count = Role::where("name", 'LIKE', "{$name}%")->count();

        return $count > 0 ? (string) "{$name}{$count}" :$name;
    }

    public function getRoleNameAttribute() : string{
        $roleName = $this->name;
        // $newName = preg_replace("/[^a-zA-Z]/", "", $roleName);
        // if(Role::where('name', $newName)
        //     ->where('guard_name', $this->guard_name)
        //     ->exists()){

        //         dd("ok");

        //         $roleName = $newName;
        // }

        return $roleName;
    }
}
