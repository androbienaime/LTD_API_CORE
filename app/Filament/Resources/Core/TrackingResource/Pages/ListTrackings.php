<?php

namespace App\Filament\Resources\Core\TrackingResource\Pages;

use App\Filament\Resources\Core\TrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrackings extends ListRecords
{
    protected static string $resource = TrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
