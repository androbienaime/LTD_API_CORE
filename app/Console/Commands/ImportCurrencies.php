<?php

namespace App\Console\Commands;

use App\Services\CurrencyServices;
use Illuminate\Console\Command;

class ImportCurrencies extends Command{
    protected $signature = 'currencies:import';
    protected $description = 'Import currencies with their current exchange rates';

    public function handle(CurrencyServices $currencies)
    {
        $this->info('Starting currency import...');
        try{
            $result = $currencies->updateRates();
            if ($result["updated"] > 0){
                $this->info("Updated {$result["updated"]} existing currencies");
            }

            if($result["error"] > 0){
                $this->warn("{$result['error']} errors encountered");
            }

            $this->info('Currency import completed successfully');
        } catch (\Exception $e) {
            $this->error('Currency import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;

    }
}
