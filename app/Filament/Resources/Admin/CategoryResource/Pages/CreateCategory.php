<?php

namespace App\Filament\Resources\Admin\CategoryResource\Pages;

use App\Filament\Resources\Admin\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
