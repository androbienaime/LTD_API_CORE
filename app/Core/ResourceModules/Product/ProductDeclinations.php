<?php

namespace App\Core\ResourceModules\Product;

use App\Models\Core\Attribute;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ProductDeclinations
{

    public static function form(){
        return Grid::make()
            ->schema([
                Fieldset::make()
                ->label(__("Declination"))
                ->schema([
                    TableRepeater::make("declinations")
                        ->relationship()
                        ->schema([
                                Select::make('value')
                                    ->relationship('values', 'value')
                                    ->label('Valeur')
                                    ->multiple()
                                    ->options(function () {
                                        // Récupérer tous les attributs avec leurs valeurs
                                        $attributes = Attribute::with('values')->get();

                                        // Organiser les valeurs par attribut
                                        $options = [];
                                        foreach ($attributes as $attribute) {
                                            $options[$attribute->name] = $attribute->values->pluck('value', 'id')->toArray();
                                        }
                                        return $options;                                
                                    })
                                    // ->saveRelationshipsUsing(function ($component, $state, $record) {
                                    //     // Synchroniser les catégories dans la table pivot sans toucher à `category_id` du modèle principal
                                    //     $record->declinationValues()->attach($state);
                                    // })
                                    ,
                                TextInput::make("price")
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->numeric(),
                                TextInput::make("quantity")
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->numeric(),
                                TextInput::make("reference"),
                                SpatieMediaLibraryFileUpload::make('declinaison_image')
                                ->multiple()
                                ->reorderable()
                                ->imageEditor()
                                ->responsiveImages()
                                ->conversion('thumb')
                                ->optimize('webp')
                                ->columnSpan('full')
                                ->imagePreviewHeight(150)
                                ->panelLayout("grid")
                                ,
                                ])
                                ->addActionLabel(__("Add declinaition"))
                                ->defaultItems(0),
                ])
            ]);
    }

}
