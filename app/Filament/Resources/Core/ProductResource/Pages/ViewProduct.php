<?php

namespace App\Filament\Resources\Core\ProductResource\Pages;

use App\Filament\Resources\Core\ProductResource;
use App\Models\Core\Product;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];


    }



}
