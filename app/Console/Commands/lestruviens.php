<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use App\Core\Class\DuplicateExistingPermissionsAcrossGuards;

class lestruviens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lestruviens:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Installation de lestruviens");

        $this->call("key:generate");
        $this->info("Clé générée");
        $this->call("optimize");
        $this->info("Optimisation terminée");

        if($this->confirm("Voulez-vous lancer les migrations ?")) {
            $this->call("migrate", ["--force" => true]);
            $this->info("Migration terminée");
        }else{
            $this->info("Migrations annulées");
            return 1;
        }

        $this->call("shield:upgrade");
        $this->info("Shield terminée");



        $this->call("optimize");
        $this->info("Optimisation terminée");

        $this->call("shield:generate", ["--all" => true,
        "--ignore-existing-policies" => true]);
        $this->info("Permissions générées");

        $this->info(__("This step can take a long time, please be patient.."));
        $this->call("db:seed");
        $this->info("Seed terminée");

        $this->info("Creating the super_admin user");
        $this->call("shield:super-admin");
        $this->info("The super_admin user has been created");

        $this->call("storage:link");
        $this->info("LesTruviens installation completed");
    }
}
