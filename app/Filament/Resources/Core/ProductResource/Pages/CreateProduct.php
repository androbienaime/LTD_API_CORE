<?php

namespace App\Filament\Resources\Core\ProductResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Core\ProductResource;
use App\Filament\Resources\Core\ProductResource\RelationManagers\DeclinationProductsRelationManager;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected static string $view = 'filament.resources.pages.create-record';
    protected function mutateFormDataBeforeCreate(array $data) : array{
        unset($data['categories']);
        return $data;
    }


    // Personnaliser les actions
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->extraAttributes([
                    'class' => 'bg-blue-600'
                ]),
            $this->getCreateAnotherFormAction()
                ->extraAttributes([
                    'class' => 'bg-blue-500'
                ]),
            $this->getCancelFormAction()
                ->extraAttributes([
                    'class' => ''
                ]),
        ];
    }
//
//    // Header personnalisé
//    public function getHeader(): ?\Illuminate\Contracts\View\View
//    {
//        return view('filament.resources.product.pages.create-product-page');
//    }

}
