<?php

namespace App\Core\ResourceModules\Product;

use App\Models\Core\Brand;
use App\Models\Core\Product;
use App\Models\Core\Attribute;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use App\Forms\Components\SelectImage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ProductDetails
{
    public static function form(){
        return Grid::make()
        ->schema([
            
        Section::make()
            ->schema([
                Grid::make()
                    ->schema([
                            SpatieMediaLibraryFileUpload::make('product_image')
                                ->multiple()
                                ->required()
                                ->reorderable()
                                ->imageEditor()
                                ->image()
                                ->responsiveImages()
                                ->conversion('thumb')
                                ->optimize('webp')
                                ->columnSpan('full')
                                ->imagePreviewHeight(150)
                                ->panelLayout("grid")
                                ,
                    ]),
                
            
                RichEditor::make('description')
                    ->maxLength(255)
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'link',
                        'redo',
                        'underline',
                        'undo',])
                    ->ColumnSpan("full"),


                Grid::make()
                    ->schema([
                        Toggle::make('is_trend')
                            ->label("Trend")
                            ->required(),
                        Toggle::make('available_market')
                            ->required(),
                        Toggle::make('status')
                            ->required(),
                        Toggle::make('is_downloaddable')
                            ->label(__("Downloaddable"))
                            ->required(),
                    ])->columns(4),
            ])->columnSpan(8),
       
        Section::make()
                ->schema([
                    Radio::make("product_with_declination")
                        ->boolean()
                        ->default(false),
                    TextInput::make('price')
                            ->required()
                            ->minValue(0)
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan("full"),
                    TextInput::make('sku')
                        ->default(uniqid())
                        ->label(__("Sku"))
                        ->unique(Product::class, column: 'sku', ignoreRecord:true)
                        ->maxLength(255),
                            Grid::make()
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__("Currency"))
                                    ->relationship("currency", "currency")
                                    ->default(1)
                                    ->required(),

                                    Select::make('product_type')
                                    ->options([
                                        "product" => "Product",
                                        "digital" => "Digital",
                                        "service" => "Service",
                                    ])
                                    ->allowHtml()
                                    ->default("product")
                                    ->required(),
                            ])->columnSpan("full"),

                            SelectTree::make('categories')
                            ->relationship('categories', 'name', 'parent_id')
                                ->placeholder(__('Please select a category'))
                                ->emptyLabel(__('Oops, no results have been found!'))
                                ->saveRelationshipsUsing(function ($component, $state, $record) {
                                    // Synchroniser les catégories dans la table pivot sans toucher à `category_id` du modèle principal
                                    $record->categories()->sync($state);
                                }),

                                Select::make('brand')
                                ->relationship('brands')
                                ->multiple()
                                ->searchable()
                                ->options(Brand::all()
                                    ->pluck('name', 'id')
                                    ->toArray()
                                )
                                ->label(__("Brand")),

                ])->columnSpan(4)
        ])->columns(12);
}

private static function declination(){
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
                    \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('declinaison_image')
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
