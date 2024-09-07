<?php

namespace App\Filament\Resources\Core\DeliveryResource\Pages;

use App\Filament\Resources\Core\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveries extends ListRecords
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
