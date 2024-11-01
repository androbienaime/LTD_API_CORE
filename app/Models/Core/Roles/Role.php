<?php

namespace App\Models\Core\Roles;

use App\Models\User;
use App\Models\Core\Shop;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Role
 * 
 * Extends Spatie's Role model to add tenant-specific functionality and custom role management.
 * This class implements multi-tenancy for role management across different authentication guards.
 *
 * @property string $name Role name
 * @property string $guard_name Guard name ('web' or 'account')
 * @property int $tenant_id ID of the tenant (User or Shop)
 * 
 * @method static createUniqueRole(string $name) Creates a unique role name by appending a number if necessary
 */
class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id'
    ];

    /**
     * Boot method with global scope for tenant separation
     * Applies tenant-specific filtering for both web and account guards
     */
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

    /**
     * Get the tenant relationship based on the current guard and context
     * 
     * @return BelongsTo Returns the relationship to either User or Shop based on guard context
     */
    public function getTenant(): BelongsTo
    {
        if ($this->guard_name == 'web') {
            return $this->belongsTo(User::class, 'tenant_id');
        } else if ($this->guard_name == 'account') {
            return $this->belongsTo(Shop::class, 'tenant_id');
        }
        
        // Fallback par défaut pour éviter le retour null
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Creates a unique role name by appending a number if the name already exists
     * 
     * @param string $name Base name for the role
     * @return string Unique role name
     */
    public static function createUniqueRole($name){
        $count = Role::where("name", 'LIKE', "{$name}%")->count();

        return $count > 0 ? (string) "{$name}{$count}" :$name;
    }

    /**
     * Get the formatted role name
     * 
     * @return string Formatted role name
     */
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
