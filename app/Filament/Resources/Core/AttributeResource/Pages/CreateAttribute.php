<?php

namespace App\Filament\Resources\Core\AttributeResource\Pages;

use App\Filament\Resources\Core\AttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function mutateFormDataBeforeCreate(array $data) : array{
        return $data;
    }
}
