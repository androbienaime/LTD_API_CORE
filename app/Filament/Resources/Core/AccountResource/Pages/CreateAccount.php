<?php

namespace App\Filament\Resources\Core\AccountResource\Pages;

use App\Filament\Resources\Core\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;
}
