<?php

namespace App\Filament\Resources\Core\DeliveryResource\Pages;

use App\Filament\Resources\Core\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDelivery extends ViewRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
