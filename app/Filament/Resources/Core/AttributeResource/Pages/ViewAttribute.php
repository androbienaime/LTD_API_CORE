<?php

namespace App\Filament\Resources\Core\AttributeResource\Pages;

use App\Filament\Resources\Core\AttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAttribute extends ViewRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
