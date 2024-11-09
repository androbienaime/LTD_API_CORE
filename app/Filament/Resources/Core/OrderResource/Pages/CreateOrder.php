<?php

namespace App\Filament\Resources\Core\OrderResource\Pages;

use App\Core\States\Order\Exception\OrderTransitionException;
use App\Filament\Resources\Core\OrderResource;
use App\Models\Core\Order;
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
        // dd($data);
        $data['reference_order'] = "FA".Carbon::now()->format("mY")."D".rand(1000, 9999);
        $data['secure_key'] =  Str::random(64);

        return $data;
    }

//    protected function afterCreate(): void
//    {
//        if($this->record->order_amount > 0){
//            $this->processOrder($this->record);
//        }
//    }





}
