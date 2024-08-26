<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\OrderResource\Pages;
use App\Filament\Resources\Admin\OrderResource\RelationManagers;
use App\Models\Admin\Order;
use App\Models\Admin\OrderStatus;
use App\Models\Admin\Product;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedSubBuilder;
use function Symfony\Component\String\match;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup() : string {
        return __("Orders");
    }

    public static function getNavigationLabel() : string{
        return __("Orders");
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Select::make('customer_id')
                        ->relationship(
                            name:'customer',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('firstname')->orderBy('lastname'),
                            )
                        ->label(__("Customer"))
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->firstname} {$record->lastname}")
                        ->required()
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Grid::make()
                                    ->schema([
                                        TextInput::make('firstname')
                                            ->label(__("First name"))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('lastname')
                                            ->label(__('Last name'))
                                            ->required()
                                            ->maxLength(255),
                                    ])->columns(2),
                            Grid::make()
                                ->schema([
                                    TextInput::make('middle_name')
                                        ->label(__("Middle Name"))
                                        ->required()
                                        ->maxLength(255),
                                    Select::make('gender')
                                        ->label(__('Gender'))
                                        ->options([
                                            "Male" => __("Male"),
                                            "Female" => __("Female"),
                                        ])
                                        ->required(),
                                ])->columns(2),
                            Grid::make()
                                ->schema([
                                    TextInput::make('identity_number')
                                        ->label(__("No ID"))
                                        ->numeric()
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ])->columns(2),
                            Grid::make()
                                ->schema([
                                    DateTimePicker::make('date_of_birth')
                                        ->label('Date of Birth')
                                        ->minDate(now()->subYear(90))
                                        ->maxDate(now()->subYear(10))
                                        ->required(),
                                ])->columns(2),

                        ]),
                Forms\Components\Select::make('status_id')
                    ->relationship(name :'status', titleAttribute:'name')
                    ->default(1)
                    ->required(),
                Section::make()
                    ->schema([
                        Forms\Components\Repeater::make("orderProducts")
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                ->relationship(name:'product', titleAttribute:'name')
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => self::labelProduct($record))
                                ->label(__("Product"))
                                ->required()
                                ->preload()
                                ->searchable()
                                ->createOptionForm([
                                    self::prod(),
                                ]),
                                TextInput::make("quantity")
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->minItems(1)
                            ->live(debounce : 500)
                            ->reorderable(true)
                            ->reorderableWithButtons()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
//                            ->itemLabel(fn (array $state): ?string =>
//                                self::labelProduct(self::getProduct($state['product_id'])) ?? null)
                            ->addActionLabel(__("Add products"))
                    ])->columns(1),

              Section::make()
                  ->schema([
                      TextInput::make('total_amount_order')
                          ->label(__("Totals"))
                          ->prefix('$')
                          ->required(),
                      Forms\Components\TextInput::make('order_amount')
                          ->label(__("Payment"))
                          ->required()
                          ->live(onBlur: true)
                          ->afterStateUpdated(function (Get $get, Set $set) {
                              self::updateBalance($get, $set);
                          })
                          ->numeric(),
                      Forms\Components\TextInput::make('balance')
                          ->label(__("Balance"))
                          ->disabled()
                          ->numeric(),
                  ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.fullname')
                    ->label(__("Full name"))
                    ->sortable(['customer.firstname', 'customer.lastname']),
                Tables\Columns\TextColumn::make('total_amount_order')
                    ->money("USD")
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_amount')
                    ->label(__("Payment"))
                    ->money("USD")
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status.name')
                    ->inverseRelationship(name:'orderStatus')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_order')
                    ->searchable(),
                Tables\Columns\TextColumn::make('merchant.firstname')
                    ->numeric()
                    ->label(__("Merchant"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('secure_key')
                    ->limit(32)
                    ->wrap()
                    ->searchable(),
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
//            RelationManagers\ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function prod(){
       return Grid::make()
           ->schema([
               Grid::make()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                            if (($get('slug') ?? '') !== Str::slug($old)) {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        })
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),

               SpatieMediaLibraryFileUpload::make('product_image')
                   ->multiple()
                   ->imageEditor()
                   ->responsiveImages()
                   ->optimize('webp')
                   ->conversion('thumb')
                   ->columnSpan('full'),

                    Grid::make()
                        ->schema([
                            Select::make('currency_id')
                                ->label(__("Currency"))
                                ->relationship("currency", "currency")
                                ->required(),

                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            TextInput::make('purchase_price')
                                ->required()
                                ->numeric(),
                        ])->columns(3),

                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->maxLength(255),

                    Grid::make()
                        ->schema([
                            TextInput::make('stock_quantity')
                                ->required()
                                ->numeric()
                                ->default(0),
                            TextInput::make('product_type')
                                ->required()
                                ->maxLength(255),
                        ])->columns(2),

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
               ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedProducts = collect($get('orderProducts'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);


        // Update the state with the new values
       // $set('subtotal', number_format($subtotal, 2, '.', ''));
        $set('total_amount_order', number_format($subtotal + ($subtotal * ($get('taxes') / 100)), 2, '.', ''));

        self::updateBalance($get, $set);
    }

    private static function updateBalance(Get $get, Set $set){
        $totals = $get("total_amount_order");
        $amount = $get("order_amount");
        $set('balance', number_format($totals - $amount, 2, '.', ''));
    }

    public static function labelProduct(Model $record){
        $price = number_format($record->price, 2, '.', '');
        return "{$record->name} (\${$price})";
    }

    public static function getProduct($id){
       return Product::all()->where("id", $id)->first();
    }
}
