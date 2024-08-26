<?php

namespace App\Filament\Resources\Admin;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Admin\Product;
use App\Models\Admin\Delivery;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Admin\ProductResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\Admin\ProductResource\RelationManagers;
use App\Filament\Resources\Admin\ProductResource\RelationManagers\DeclinationProductsRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                            Tab::make(__("HOME"))
                            ->schema([
                                self::productFormHome()
                            ])
                            ->icon("heroicon-o-home"),

                            Tab::make(__("Declination"))
                            ->schema([
                                self::declination()
                            ]),
                            Tab::make(__("Shipping"))
                            ->schema([
                                self::shipping()
                            ])
                        ])->columnSpan("full")
                
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                SpatieMediaLibraryImageColumn::make('product_image')
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->conversion('thumb'),
              
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency.symbol')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_downloadable')
                    ->boolean(),
                Tables\Columns\IconColumn::make('available_market')
                    ->boolean(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_downloaddable')
                    ->boolean(),
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
                                                ->required(),
                                            TextInput::make('purchase_price')
                                                ->numeric(),
                                        ])->columnSpan("full"),

                                        TextInput::make('slug')
                                        ->required()
                                        ->maxLength(255),

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
        return Repeater::make("declinationProducts")
                    ->relationship()
                    ->schema([
                        // Select::make("attribute")
                        //     ->relationship(name:"attribute", titleAttribute:"name")
                        //     ->required()

                    ]);
    }

    private static function shipping(){
        return 
            Repeater::make("deliveryProducts")
                ->relationship()
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
                                ->numeric(),
                            Select::make("delivery_mode")
                                ->options([
                                    //
                                ])
                       
                
                    
                    ->columns(2)
                ])
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array{
                      return self::saveDelivery($data);
                });
    }

    private static function saveDelivery(array $data): array{
        if($data['width'] || $data['heigth'] || $data['weigth'] || 
            $data['depth'] || $data['costs']){

            $delivery = Delivery::firstOrCreate(
                ["width" => $data['width']],
                ["heigth" => $data['heigth']],
                ["weigth" => $data['weigth']],
                ["depth" => $data['depth']],
                ["costs" => $data['costs']],
                ["delivery_mode" => $data['delivery_mode']],
            );
            
            $data["delivery_id"] = $delivery->id;
            unset($data["width"]);
            unset($data["heigth"]);
            unset($data["weigth"]);
            unset($data["depth"]);
            unset($data["costs"]);
            unset($data["delivery_mode"]);
        }

        
        return $data;
    }
}
