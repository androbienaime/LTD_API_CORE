<?php

namespace App\Filament\Resources\Core\CarrierResource\Pages;

use App\Filament\Resources\Core\CarrierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarriers extends ListRecords
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
