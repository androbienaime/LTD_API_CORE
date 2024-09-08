<?php

namespace App\Filament\Resources\Core\OrderStatusResource\Pages;

use App\Filament\Resources\Core\OrderStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderStatus extends CreateRecord
{
    protected static string $resource = OrderStatusResource::class;
}
