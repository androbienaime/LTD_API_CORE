<?php

namespace App\Filament\Resources\Admin\OrderResource\Pages;

use App\Filament\Resources\Admin\OrderResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.orders.pages.create-order';

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['reference_order'] = "FA".Carbon::now()->format("mY")."D".rand(1000, 9999);
        $data['secure_key'] =  Str::random(64);

        return $data;
    }



}
