<?php

namespace App\Filament\Resources\Core\MerchantResource\Pages;

use App\Filament\Resources\Core\MerchantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchant extends ViewRecord
{
    protected static string $resource = MerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
