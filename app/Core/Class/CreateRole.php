<?php

namespace App\Core\Class;

use Filament\Facades\Filament;
use App\Models\Core\Roles\Role;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;

class CreateRole
{
    protected string $guard_name;
    protected array $excludedResources = [];
    protected string $name;

    public function __construct($name, $guard_name, $excludedResources = [])
    {
        $this->name = $name;
        $this->guard_name = $guard_name;
        $this->excludedResources = $excludedResources;
    }
    public function handle()
    {
        if(Role::where("name", $this->name)
                ->where("guard_name", $this->guard_name)
                ->exists()){
            return;
        }
        // Créer le rôle super_admin s'il n'existe pas
        $superAdminRole = Role::firstOrCreate(['name' => Role::createUniqueRole($this->name), "guard_name" => $this->guard_name]);

        $permissions = [];


        $prefixes = config("filament-shield")["permission_prefixes"]["resource"];
        $resources = $this->getResourceFolders();
        $AllPermission = Permission::all()->pluck('name')->toArray();

        foreach($resources as $key => $resource){
            foreach($prefixes as $prefix){
                $permissions[] = $prefix . "_" . $key;
            }
        }

        $permissions = array_intersect($permissions, $AllPermission);

        // Duplication des permissions entre les guards s'il n'existe pas déjà
        $duplicate = new DuplicateExistingPermissionsAcrossGuards();
        $duplicate->duplicate();

        Artisan::call("permission:cache-reset");

        // Assigner toutes les permissions au rôle super_admin
        $superAdminRole->syncPermissions($permissions);

        return $superAdminRole;
    }


    public function getResourceFolders(): array
    {
        return collect(FilamentShield::getResources())
        ->filter(function ($entity){
            $excludedResources = [];
            // Exemple: exclure certaines ressources par leur nom
                $excludedResources = config("ltsp.excludedResources")["account"];
                $excluded = array_merge($this->excludedResources, $excludedResources);

            return !in_array(class_basename($entity['fqcn']), $excluded);
        })
        ->toArray();
    }


}
