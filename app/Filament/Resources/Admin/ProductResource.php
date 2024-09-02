<?php

namespace App\Filament\Resources\Admin;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Admin\Value;
use Illuminate\Support\Str;
use App\Models\Admin\Product;
use App\Models\Admin\Delivery;
use App\Models\Admin\Attribute;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\SpatieTagsInput;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Admin\ProductResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\Admin\ProductResource\RelationManagers;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Filament\Resources\Admin\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\Admin\ProductResource\RelationManagers\DeclinationProductsRelationManager;
use App\Filament\Resources\DeliveryProductRelationManagerResource\RelationManagers\DeliveryProductRelationManager;

class ProductResource extends Resource
{
    use FillTableToManyTrait;
    protected static ?string $model = Product::class;

    // protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup() : string {
        return __("Catalogs");
    }

    public static function getNavigationLabel() : string{
        return __("Product");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                        if (($get('slug') ?? '') !== Str::slug($old)) {
                            return;
                        }

                        $set('slug', Product::createUniqueSlug($state));
                    })
                    ->columnSpan("full")
                    ->maxLength(255),

                    Tabs::make("Tabs")
                        ->tabs([
                            Tab::make(__("Details"))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                self::productFormHome()
                            ])
                            ->hiddenOn(DeliveryProductRelationManager::class)
                            ->icon('heroicon-o-information-circle'),

                            Tab::make(__("Declination"))
                            ->schema([
                                self::declination()
                            ])
                            ->hiddenOn(DeliveryProductRelationManager::class)
                            ->icon('heroicon-o-cursor-arrow-ripple'),
                            Tab::make(__("Shipping"))
                            ->schema([
                               self::shipping()
                                                        
                            ])
                            ->icon("heroicon-o-truck"),
                            Tab::make("SEO")
                                ->Icon("heroicon-o-magnifying-glass")
                                ->schema([
                                    TextInput::make('slug')
                                        ->live()
                                        ->required()
                                        ->maxLength(255),
                                       
                                ])
                        ])->columnSpan("full")
                
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('name')
                    ->label(__("Name of product"))
                    ->verticallyAlignStart()
                    ->wrap()
                    ->lineClamp(2)
                    ->columnSpanFull()
                    ->searchable(),
                SpatieMediaLibraryImageColumn::make('product_image')
                    ->label(__("Image"))
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->conversion('thumb'),
              
                Tables\Columns\TextColumn::make('slug')
                    ->label(__("Product Slug"))
                    ->wrap()
                    ->lineClamp(2)
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->verticallyAlignStart()
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop_id')
                ->label(__('Shop'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__("Stock"))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->label(__("Type"))
                    ->searchable(),
                ToggleColumn::make('is_downloadable')
                    ->label(__("Downloadable")),
                ToggleColumn::make('available_market')
                    ->label(__("Available Market")),
                ToggleColumn::make('status')
                    ->label("status"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DeliveryProductRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function productFormHome(){
        return Grid::make()
                    ->schema([
                        
                    Section::make()
                        ->schema([
                            Grid::make()
                                ->schema([
                                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('product_image')
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
                                    Forms\Components\Toggle::make('is_downloadable')
                                        ->required(),
                                    Forms\Components\Toggle::make('available_market')
                                        ->required(),
                                    Forms\Components\Toggle::make('status')
                                        ->required(),
                                    Forms\Components\Toggle::make('is_downloaddable')
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
                                        ->numeric()
                                        ->prefix('$')
                                        ->columnSpan("full"),
                                        Grid::make()
                                        ->schema([
                                            Select::make('currency_id')
                                                ->label(__("Currency"))
                                                ->relationship("currency", "currency")
                                                ->default(1)
                                                ->required(),
                                            TextInput::make('purchase_price')
                                                ->numeric(),
                                        ])->columnSpan("full"),

                                        SelectTree::make('categories')
                                        ->relationship('categories', 'name', 'parent_id')
                                            ->placeholder(__('Please select a category'))
                                            ->emptyLabel(__('Oops, no results have been found!'))
                                            ->saveRelationshipsUsing(function ($component, $state, $record) {
                                                // Synchroniser les catégories dans la table pivot sans toucher à `category_id` du modèle principal
                                                $record->categories()->sync($state);
                                            }),
                                        Grid::make()
                                        ->schema([
                                            TextInput::make('stock_quantity')
                                                ->required()
                                                ->numeric()
                                                ->default(0),
                                            TextInput::make('product_type')
                                                ->maxLength(255),
                                        ])->columns(2),
        
                            ])->columnSpan(4)
                    ])->columns(12);
    }

    private static function declination(){
        return TableRepeater::make("declinations")
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
                            ->defaultItems(0);
    }

    private static function shipping(){
        return 
            Repeater::make("deliveryProducts")
                ->relationship()
                ->schema([          
                    Grid::make("delivery_id")
                    ->label(__("Delivery"))
                    ->relationship("delivery", "id")
                        ->schema([
                            TextInput::make("width")
                                ->numeric(),
                            TextInput::make("heigth")
                                ->numeric(),
                            TextInput::make("depth")
                                ->numeric(),
                            TextInput::make("weigth")
                                ->numeric(),
                            TextInput::make("costs")
                                ->required()
                                ->default(0)
                                ->numeric(),
                            Select::make("carrier_id")
                                ->relationship(name:"carrier", titleAttribute:"carrier_name")
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->carrier_name}")
                                ->label(__("Carrier"))
                                ->preload()
                                ->searchable()
                        ])->columns(2)
                    
                ])
                ->grid(2)
                ->addActionLabel(__("Add Shipping"))
                ->defaultItems(0)
                ->collapsed(false)
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array{
                   return self::processFillTable($data, Delivery::class, "delivery_id");
                });
    }

}
