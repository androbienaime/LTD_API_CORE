<?php

namespace App\Filament\Resources\Admin\AttributeResource\Pages;

use App\Filament\Resources\Admin\AttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function mutateFormDataBeforeCreate(array $data) : array{
        return $data;
    }
}
