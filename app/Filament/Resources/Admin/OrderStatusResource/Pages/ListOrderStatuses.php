<?php

namespace App\Filament\Resources\Admin\OrderStatusResource\Pages;

use App\Filament\Resources\Admin\OrderStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderStatuses extends ListRecords
{
    protected static string $resource = OrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
