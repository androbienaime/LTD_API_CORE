<?php

namespace App\Filament\Resources\Admin\AttributeResource\Pages;

use App\Filament\Resources\Admin\AttributeResource;
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
