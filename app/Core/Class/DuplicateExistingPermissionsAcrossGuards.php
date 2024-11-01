<?php

namespace App\Core\Class;

use Spatie\Permission\Models\Permission;

class DuplicateExistingPermissionsAcrossGuards
{

    public function duplicate($sourceGuard = 'web', $targetGuard = 'account') {

        // Si les guards existent, on les utilise, sinon on les crée
        if (Permission::whereIn('guard_name', [$sourceGuard, $targetGuard])->exists()) {
            $sourceGuard = Permission::where('guard_name', $sourceGuard)->exists() ? $sourceGuard : $targetGuard;
            $targetGuard = $sourceGuard === $sourceGuard ? $targetGuard : $sourceGuard;
        } else {
            return [];
        }
        
        // Récupérer les permissions existantes du guard source
        $existingPermissions = Permission::where('guard_name', $sourceGuard)->get();
    
        // Tableau pour stocker les permissions dupliquées
        $duplicatedPermissions = [];
    
        foreach ($existingPermissions as $permission) {
            // Vérifier si une permission identique existe déjà pour le guard cible
            $existingTargetPermission = Permission::where('name', $permission->name)
                ->where('guard_name', $targetGuard)
                ->first();
    
            // Si la permission n'existe pas pour le guard cible, la créer
            if (!$existingTargetPermission) {
                $newPermission = Permission::create([
                    'name' => $permission->name,
                    'guard_name' => $targetGuard
                ]);
                
                $duplicatedPermissions[] = $newPermission->name;
            }
        }
    
        return $duplicatedPermissions;
    }
}
