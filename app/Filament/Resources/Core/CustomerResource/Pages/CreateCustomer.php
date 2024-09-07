<?php

namespace App\Filament\Resources\Core\CustomerResource\Pages;

use App\Filament\Resources\Core\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
