<?php

namespace App\Filament\Resources\Admin\ProductResource\Pages;

use Filament\Actions;
use App\Models\Admin\Delivery;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Admin\ProductResource;
use App\Filament\Resources\Admin\ProductResource\RelationManagers\DeclinationProductsRelationManager;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    // protected static string $view = "filament.product.pages.create-product-page";

    // protected function handleRecordCreation(array $data): Model{
    //     dd(Filament::getTenant());
    // }
    protected function mutateFormDataBeforeSave(array $data) : array{
        // dd($data);
        // foreach ($data["deliveryProducts"] as $deliveryProducts){
        //     if(isset($deliveryProducts['delivery'])){
        //             $delivery = Delivery::firstOrCreate(
        //                 $this->parameterDelivery($data, "width"),
        //                 $this->parameterDelivery($data, "heigth"),
        //                 $this->parameterDelivery($data, "weigth"),
        //                 $this->parameterDelivery($data, "costs"),
        //                 $this->parameterDelivery($data, "depht"),
        //                 $this->parameterDelivery($data, "delivery_mode"),
        //             );

        //             $data["delivery_id"] = $delivery->id;
        //             unset($data["deliveryProducts"]); 

        //     }
        // }

        return $data;
    }

    private function parameterDelivery($data, $params){
            if(!empty($data['deliveryProducts'][$params])){
                return [$params => $data['deliveryProducts'][$params]];
            }
    }

    // protected function getFormActions(): array
    // {
    //     return [
    //         ButtonAction::make('create')
    //             ->label('Create')
    //             ->action('create')
    //             ->extraAttributes(['class' => 'filament-button']),
            
    //         ButtonAction::make('save')
    //             ->label('Save')
    //             ->action('save')
    //             ->extraAttributes(['class' => 'filament-button']),
    //     ];
    // }
 
}
