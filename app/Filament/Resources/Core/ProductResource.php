<?php

namespace App\Filament\Resources\Core;

use App\Core\ResourceModules\HasResourceStatus;
use App\Core\ResourceModules\Product\ProductDeclinations;
use App\Core\ResourceModules\Product\ProductDetails;
use App\Core\ResourceModules\Product\ProductSeo;
use App\Core\ResourceModules\Product\ProductShippings;
use App\Core\ResourceModules\Product\ProductStockAndPrices;
use App\Core\States\GeneralStatus\ActiveState;
use App\Core\States\GeneralStatus\InactiveState;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Core\Product;
use App\Models\Core\Category;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Resources\Core\ProductResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Filament\Resources\Core\ProductResource\RelationManagers\DeliveryProductRelationManager;

class ProductResource extends Resource
{
    use FillTableToManyTrait, HasResourceStatus;
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
                        $set('slug', Product::createUniqueSlug($get('name')));
                    })
                    ->columnSpan("full")
                    ->maxLength(255),

                    Tabs::make("Tabs")
                        ->tabs([
                            Tab::make(__("Details"))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                ProductDetails::form()
                            ])
                            ->hiddenOn(DeliveryProductRelationManager::class)
                            ->icon('heroicon-o-information-circle'),

                            Tab::make(__("Declination"))
                            ->schema([
                                ProductDeclinations::form()
                            ])
                            ->hiddenOn(DeliveryProductRelationManager::class)
                            ->icon('heroicon-o-cursor-arrow-ripple'),

                            Tab::make(__("Stock & Price"))
                            ->schema([
                                ProductStockAndPrices::form()
                            ])
                            ->icon('heroicon-o-banknotes'),
                            Tab::make(__("Shipping"))
                            ->schema([
                               ProductShippings::form()

                            ])
                            ->icon("heroicon-o-truck"),
                            Tab::make("SEO")
                                ->Icon("heroicon-o-magnifying-glass")
                                ->schema([
                                    ProductSeo::form()

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
                    ->searchable()
                    ->tooltip(function (TextColumn $column, Product $product): ?string {
                        $state = $column->getState();
                        $message = '';

                        // Check if the state exceeds the character limit
                        if (strlen($state) > $column->getCharacterLimit()) {
                            $message = $state;
                        }

                        // Check if the product description exceeds the length limit
                        if (strlen($product->description) > 40) {
                            $message .= "\n\nDescription:\n" . strip_tags(Str::limit($product->description, 255));
                        }

                        // Return null if no message to display or if both conditions are satisfied
                        return $message === '' ? null : $message;
                    }),


                SpatieMediaLibraryImageColumn::make('product_image')
                    ->label(__("Image"))
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->limitedRemainingText()
                    ->conversion('thumb'),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__("Preview"))
                    ->formatStateUsing(static function($state){
                        return __("Preview");
                    })
                    ->url(fn($record) => $record->slug)
                    ->openUrlInNewTab()
                    ->icon("heroicon-m-arrow-top-right-on-square")
                    ->iconPosition(IconPosition::After)
                    ->color("primary")
                    ->tooltip(fn(Model $record) => $record->slug)
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->state(fn(Product $product) => ($product->price) - ($product->productDiscount ? $product->productDiscount->discount: 0))
                    ->description(fn(Product $product) => '(Price:'.number_format($product->price, 2). ')-Discount:' . number_format($product->productDiscount ? $product->productDiscount->discount: 0))
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__("Stock"))
                    ->formatStateUsing(static function (TextColumn $column, $state, Product $product)  {
                        $state = ($state == 0) ?: __("Out stock");
                        return $product->has_unlimited_stock ? "Unlimited": $state;
                    })
                    ->badge()
                    ->color("none")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('categories.name')
                ->badge()
                ->label(__("Category"))
                ->inline()
                ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('brands.name')
                ->badge()
                ->label(__("Brand"))
                ->inline()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shop_id')
                ->label(__('Shop'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_downloadable')
                    ->label(__("Downloadable"))
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('available_market')
                    ->label(__("Available Market"))
                    ->toggleable(isToggledHiddenByDefault: true),
                    self::TablesStatus()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                ->relationship("categories", "name")
                ->multiple()
                ->preload()
                ->label(__("Category"))
                ->searchable()
                ->options(Category::all()
                    ->pluck('name', 'id')
                    ->toArray()
                ),
                Tables\Filters\TernaryFilter::make('status'),
                Tables\Filters\TernaryFilter::make('is_trend'),
                Tables\Filters\TernaryFilter::make('is_in_stock'),
                Tables\Filters\TernaryFilter::make('has_unlimited_stock'),

            ])
            ->groups([
                Tables\Grouping\Group::make('product_type')
                ->label(__('Type'))
            ])
            ->actions([
                ActionGroup::make([
                    self::ActionStatus(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->iconButton()
            ], /* position: ActionsPosition::BeforeCells */)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort("updated_at", "desc");
    }

    public static function getRelations(): array
    {
        return [
            // DeliveryProductRelationManager::class,
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

}
