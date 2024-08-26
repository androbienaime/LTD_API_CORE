<?php

namespace App\Filament\Resources\Admin\CurrencyResource\Pages;

use App\Filament\Resources\Admin\CurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
