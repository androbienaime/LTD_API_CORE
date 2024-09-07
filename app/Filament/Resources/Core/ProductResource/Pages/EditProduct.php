<?php

namespace App\Filament\Resources\Core\ProductResource\Pages;

use App\Filament\Resources\Core\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data) : array{
        unset($data['categories']);
        return $data;
    }


}
