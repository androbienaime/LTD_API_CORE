<?php

namespace App\Filament\Resources\Admin\OrderStatusResource\Pages;

use App\Filament\Resources\Admin\OrderStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderStatus extends ViewRecord
{
    protected static string $resource = OrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
