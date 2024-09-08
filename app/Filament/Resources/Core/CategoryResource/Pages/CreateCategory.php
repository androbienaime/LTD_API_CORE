<?php

namespace App\Filament\Resources\Core\CategoryResource\Pages;

use App\Filament\Resources\Core\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
