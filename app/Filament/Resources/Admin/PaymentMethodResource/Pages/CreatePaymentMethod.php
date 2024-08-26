<?php

namespace App\Filament\Resources\Admin\PaymentMethodResource\Pages;

use App\Filament\Resources\Admin\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
