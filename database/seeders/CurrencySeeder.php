<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('currencies')->insert($this->currencies());
    }

    public function currencies(): array
    {
        return $currencies = [
            ['iso_code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'exchange_rate' => 1.0, 'country' => 'United States', 'is_active' => true],
            ['iso_code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.85, 'country' => 'Eurozone', 'is_active' => true],
            ['iso_code' => 'HTG', 'name' => 'Haitian Gourde', 'symbol' => 'G', 'exchange_rate' => 96.5, 'country' => 'Haiti', 'is_active' => true],
            ['iso_code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate' => 110.45, 'country' => 'Japan', 'is_active' => false],
            ['iso_code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate' => 0.76, 'country' => 'United Kingdom', 'is_active' => false],
            ['iso_code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'exchange_rate' => 1.35, 'country' => 'Australia', 'is_active' => false],
            ['iso_code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate' => 1.25, 'country' => 'Canada', 'is_active' => false],
            ['iso_code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'exchange_rate' => 0.92, 'country' => 'Switzerland', 'is_active' => false],
            ['iso_code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'exchange_rate' => 6.45, 'country' => 'China', 'is_active' => false],
            ['iso_code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'exchange_rate' => 8.65, 'country' => 'Sweden', 'is_active' => false],
            ['iso_code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'exchange_rate' => 1.4, 'country' => 'New Zealand', 'is_active' => false],
            ['iso_code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'exchange_rate' => 20.01, 'country' => 'Mexico', 'is_active' => false],
            ['iso_code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'exchange_rate' => 1.35, 'country' => 'Singapore', 'is_active' => false],
            ['iso_code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'exchange_rate' => 7.75, 'country' => 'Hong Kong', 'is_active' => false],
            ['iso_code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'exchange_rate' => 8.6, 'country' => 'Norway', 'is_active' => false],
            ['iso_code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'exchange_rate' => 1150.0, 'country' => 'South Korea', 'is_active' => false],
            ['iso_code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺', 'exchange_rate' => 8.35, 'country' => 'Turkey', 'is_active' => false],
            ['iso_code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'exchange_rate' => 73.5, 'country' => 'Russia', 'is_active' => false],
            ['iso_code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate' => 74.5, 'country' => 'India', 'is_active' => false],
            ['iso_code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'exchange_rate' => 5.4, 'country' => 'Brazil', 'is_active' => false],
            ['iso_code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'exchange_rate' => 15.0, 'country' => 'South Africa', 'is_active' => false],
            ['iso_code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱', 'exchange_rate' => 50.1, 'country' => 'Philippines', 'is_active' => false],
            ['iso_code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'exchange_rate' => 3.85, 'country' => 'Poland', 'is_active' => false],
            ['iso_code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'exchange_rate' => 14300.0, 'country' => 'Indonesia', 'is_active' => false],
            ['iso_code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'exchange_rate' => 33.0, 'country' => 'Thailand', 'is_active' => false],
            ['iso_code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'exchange_rate' => 4.15, 'country' => 'Malaysia', 'is_active' => false],
            ['iso_code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'exchange_rate' => 3.75, 'country' => 'Saudi Arabia', 'is_active' => false],
            ['iso_code' => 'AED', 'name' => 'United Arab Emirates Dirham', 'symbol' => 'د.إ', 'exchange_rate' => 3.67, 'country' => 'United Arab Emirates', 'is_active' => false],
            ['iso_code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'exchange_rate' => 98.5, 'country' => 'Argentina', 'is_active' => false],
            ['iso_code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'exchange_rate' => 6.3, 'country' => 'Denmark', 'is_active' => false],
            ['iso_code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => '£', 'exchange_rate' => 15.7, 'country' => 'Egypt', 'is_active' => false],
            ['iso_code' => 'ILS', 'name' => 'Israeli New Shekel', 'symbol' => '₪', 'exchange_rate' => 3.2, 'country' => 'Israel', 'is_active' => false],
            ['iso_code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'exchange_rate' => 750.0, 'country' => 'Chile', 'is_active' => false],
            ['iso_code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'exchange_rate' => 168.5, 'country' => 'Pakistan', 'is_active' => false],
            ['iso_code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'exchange_rate' => 84.8, 'country' => 'Bangladesh', 'is_active' => false],
            ['iso_code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'exchange_rate' => 310.0, 'country' => 'Hungary', 'is_active' => false],
            ['iso_code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'exchange_rate' => 22.0, 'country' => 'Czech Republic', 'is_active' => false],
            // Ajouter d'autres devises ici si nécessaire
        ];

    }
}
