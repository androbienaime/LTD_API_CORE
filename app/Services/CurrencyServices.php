<?php

namespace App\Services;

use App\Models\Core\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyServices
{
    private $apiKey;
    private $apiUrl;

    private $baseCurrency;

    public function __construct(){
        $this->apiUrl = config("ltsp.currency.api_url");
        $this->apiKey = config("ltsp.currency.api_key");
        $this->baseCurrency = config("ltsp.currency.base_currency");
    }

    public function updateRates()
    {
        $error =0;
        $updated =0;
        try {
            $response = Http::get($this->apiUrl."?apikey=".$this->apiKey);

            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];

                foreach ($rates as $code => $rate) {
                    Currency::where('iso_code', $code)->update([
                        'exchange_rate' => $rate,
                    ]);

                    $updated++;
                }

            } else {
                // Gestion des erreurs HTTP
                Log::error("Erreur HTTP : " . $response->status());
                $error++;
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Gestion des erreurs liées à la requête HTTP
            Log::error("Erreur de requête HTTP : " . $e->getMessage());
            $error++;
        } catch (\Exception $e) {
            // Gestion des autres erreurs
            Log::error("Une erreur est survenue : " . $e->getMessage());
            $error++;
        }

        return ["updated" => $updated, "error" => $error];
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $fromCurrency = $this->getCurrency($from);
        $toCurrency = $this->getCurrency($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException('Invalid currency codes');
        }

        // Convert through base currency (USD)
        $inUSD = $amount / $fromCurrency->exchange_rate;
        return $inUSD * $toCurrency->exchange_rate;
    }

    protected function getCurrency(string $iso_code): ?Currency
    {
        return Cache::remember(
            "currency_{$iso_code}",
            now()->addHour(),
            fn () => Currency::where('iso_code', $iso_code)->first()
        );
//        return Currency::where('iso_code', $iso_code)->first();
    }

    protected function clearCache(): void
    {
        Cache::tags(['currencies'])->flush();
    }

}
