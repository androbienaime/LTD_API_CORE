<?php

namespace Database\Seeders;

use App\Core\Class\CreateRole;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = new CreateRole("super-admin", "account");
        $role->handle();

        $excludedResources = config("ltsp.excludedResources")["role-account"];
        if(is_null($excludedResources) || empty($excludedResources)){
            return;
        }

        foreach($excludedResources as $key => $value){
            if(!empty($value) && config("ltsp.".$key) == true){
                $role = new CreateRole($key, "account", $value);
                $role->handle();
            }
        }
    }
}
