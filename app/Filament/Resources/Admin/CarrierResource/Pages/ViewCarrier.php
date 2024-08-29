<?php

namespace App\Filament\Resources\Admin\CarrierResource\Pages;

use App\Filament\Resources\Admin\CarrierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCarrier extends ViewRecord
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
