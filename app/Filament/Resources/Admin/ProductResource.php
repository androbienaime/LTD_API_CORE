<?php

namespace App\Filament\Resources\Admin;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Admin\Brand;
use App\Models\Admin\Value;
use Illuminate\Support\Str;
use App\Models\Admin\Product;
use App\Models\Admin\Category;
use App\Models\Admin\Delivery;
use App\Models\Admin\Attribute;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
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
                    ->lazy()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        // if (($get('slug') ?? '') !== Str::slug($old)) {
                        //     return;
                        // }

                        $set('slug', Product::createUniqueSlug($get('name')));
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
                            
                            Tab::make(__("Stock & Price"))
                            ->schema([
                                self::stockAndPrice()
                            ])
                            ->icon('heroicon-o-banknotes'),
                            Tab::make(__("Shipping"))
                            ->schema([
                               self::shipping()
                                                        
                            ])
                            ->icon("heroicon-o-truck"),
                            Tab::make("SEO")
                                ->Icon("heroicon-o-magnifying-glass")
                                ->schema([
                                    self::seo()
                                       
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
                    ->description(fn(Product $product) => new HtmlString(
                        "<span style='font-size:10px'>".strip_tags(Str::limit($product->description, 40)."</span>")))
                    ->verticallyAlignStart()
                    ->wrap()
                    ->limit(20)
                    ->lineClamp(2)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'width: 200px;'])
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
                    ->badge()
                    ->state(fn(Product $product) => match ($product->product_type) {
                        'product' => 'Product',
                        'digital' => 'Digital',
                        'service' => 'Service',
                    })
                    ->color(fn(Product $product) => match ($product->product_type) {
                        'product' => 'primary',
                        'digital' => 'success',
                        'service' => 'warning',
                    })
                    ->icon(fn(Product $product) => match ($product->product_type) {
                        'product' => 'heroicon-s-shopping-cart',
                        'digital' => 'heroicon-s-cloud',
                        'service' => 'heroicon-s-cog',
                    })
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
                                            ->required()
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
                                    Forms\Components\Toggle::make('is_trend')
                                        ->label("Trend")
                                        ->required(),
                                    Forms\Components\Toggle::make('available_market')
                                        ->required(),
                                    Forms\Components\Toggle::make('status')
                                        ->required(),
                                    Forms\Components\Toggle::make('is_downloaddable')
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

                                            Forms\Components\Select::make('brand')
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
                                ->minValue(0)
                                ->numeric(),
                            TextInput::make("heigth")
                                ->minValue(0)
                                ->numeric(),
                            TextInput::make("depth")
                                ->minValue(0)
                                ->numeric(),
                            TextInput::make("weigth")
                                ->minValue(0)
                                ->numeric(),
                            TextInput::make("costs")
                                ->minValue(0)
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

    private static function seo(){
        return Grid::make()
            ->schema([
                TextInput::make('slug')
                    ->live()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Grid::make("ltspSeo")
                    ->relationship("ltspSeo", "id")
                    ->schema([
                            TextInput::make('meta_title')
                                ->live()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            RichEditor::make('meta_description')
                            ->columnSpanFull()
                            ->label(__("Meta Description"))
                            ->columnSpanFull(),
                        
                            Select::make('is_redirection')
                                ->options(
                                    [
                                        "0" => __("No"),
                                        "1" => __("Yes"),
                                    ]
                                )
                                ->required()
                                ->default("0")
                                ->label("Redirection"),
                            
                                Select::make('category_id')
                                ->label('Category')
                                ->searchable()
                                ->options(
                                    Category::all()
                                    ->pluck('name', 'id')
                                    ->toArray()
                                ),
                            Forms\Components\SpatieTagsInput::make('product_tags')
                                ->columnSpanFull()
                                ->label("Tags"),
                ])->columns(2)
               
            ]);

    }

    private static function stockAndPrice(){
        return Grid::make()
                ->schema([  
                    Fieldset::make()
                    ->label(__("Gestion Stock"))
                        ->schema([
                        Grid::make()
                        ->schema([
                            Forms\Components\Toggle::make('is_in_stock')
                                ->label(__("In Stock"))
                                ->live()
                                ->default(false)
                                ->required(),
                            Forms\Components\Toggle::make('has_unlimited_stock')
                                ->label("Unlimited Stock")
                                ->live()
                                ->hidden(fn (Forms\Get $get) => !$get('is_in_stock'))
                                ->default(true)
                                ->required(),
                                Forms\Components\Toggle::make('has_stock_alert')
                                ->label(__("Stock Alert"))
                                ->live()
                                ->hidden(fn (Forms\Get $get) => !$get('is_in_stock'))
                                ->default(false)
                                ->required(),
                           
                        ])->columns(3),
                        Grid::make()
                        ->schema([
                            TextInput::make('stock_quantity')
                            ->live()
                            ->hidden(fn (Forms\Get $get) => !$get('is_in_stock') || $get('has_unlimited_stock'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpanFull(),

                            TextInput::make('min_stock_alert')
                            ->live()
                            ->hidden(fn (Forms\Get $get) => !$get('is_in_stock') || !$get('has_stock_alert'))
                            ->minValue(0)
                            ->numeric()
                            ->default(0),
                            TextInput::make('max_stock_alert')
                            ->live()
                            ->minValue(0)
                            ->hidden(fn (Forms\Get $get) => !$get('is_in_stock') || !$get('has_stock_alert'))
                            ->numeric()
                            ->default(0),

                        ])->columns(2)

                       
                        ]),
                        Fieldset::make()
                        ->label(__("Price"))
                            ->schema([
                            Grid::make()
                            ->schema([
                                Forms\Components\Toggle::make('has_multi_price')
                                    ->label(__("Multi price"))
                                    ->live()
                                    ->default(false)
                                    ->required(),
                            TextInput::make('purchase_price')
                                ->numeric(),
                            ]),
                        ]),
                        Fieldset::make()
                        ->label(__("Discount"))
                        ->schema([
                            Forms\Components\Toggle::make('has_discount')
                            ->live(),
                            Grid::make()
                            ->relationship("productDiscount", "id")
                            ->live()
                            ->hidden(fn (Forms\Get $get) => !$get('has_discount'))
                            ->schema([
                                TextInput::make("discount")
                                ->live()
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->label(__("Discount"))
                                ->prefix("%")
                                ->columnSpanFull(),
                                Grid::make()
                                    ->schema([
                                        DateTimePicker::make('start_date')
                                        ->live()
                                            ->rule('after:now')
                                            ->label(__("Start Date")),
                                        DateTimePicker::make('end_date')
                                        ->live()
                                        ->rule('after:now')
                                        ->label(__("End Date")),
                                    ])->columns(2)
                            ])
                        ])

                ]);
    }

}
