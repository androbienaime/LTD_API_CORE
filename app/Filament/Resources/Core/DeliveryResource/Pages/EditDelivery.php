<?php

namespace App\Filament\Resources\Core\DeliveryResource\Pages;

use App\Filament\Resources\Core\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
