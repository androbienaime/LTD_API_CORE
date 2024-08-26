<?php

namespace App\Filament\Resources\Admin\OrderStatusResource\Pages;

use App\Filament\Resources\Admin\OrderStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderStatus extends CreateRecord
{
    protected static string $resource = OrderStatusResource::class;
}
