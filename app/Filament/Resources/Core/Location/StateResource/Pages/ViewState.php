<?php

namespace App\Filament\Resources\Core\Location\StateResource\Pages;

use App\Filament\Resources\Core\Location\StateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewState extends ViewRecord
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
