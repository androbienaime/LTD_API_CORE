<?php

namespace App\Filament\Resources\Core\PaymentMethodResource\Pages;

use App\Filament\Resources\Core\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
