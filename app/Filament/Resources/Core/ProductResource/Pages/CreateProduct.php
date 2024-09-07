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

    protected function mutateFormDataBeforeCreate(array $data) : array{
        unset($data['categories']);
        return $data;
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
