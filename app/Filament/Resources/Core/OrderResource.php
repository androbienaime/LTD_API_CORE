<?php

namespace App\Filament\Resources\Core;

use Closure;
use Exception;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Core\Order;
use Filament\Tables\Table;
use App\Core\Class\Coupons;
use App\Models\Core\Coupon;
use Illuminate\Support\Str;
use App\Models\Core\Product;
use App\Models\Core\Delivery;
use App\Models\Location\City;
use App\Models\Core\Attribute;
use App\Models\Location\State;
use Filament\Facades\Filament;
use App\Core\Trait\ProductTrait;
use App\Models\Core\Declination;
use App\Models\Core\OrderStatus;
use App\Models\Location\Country;
use Filament\Resources\Resource;
use App\Core\Class\GenerateFields;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Components\Tab;
use App\Core\Trait\GenerateFieldsTrait;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Builder\Block;
use Stevebauman\Purify\Facades\Purify;
use function Symfony\Component\String\match;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Core\ResourceModules\Product\ProductSeo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Core\ResourceModules\Product\ProductDetails;
use App\Filament\Resources\Core\OrderResource\Pages;
use App\Core\ResourceModules\Product\ProductShippings;
use App\Core\ResourceModules\Product\ProductDeclinations;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Core\ResourceModules\Product\ProductStockAndPrices;
use App\Core\Trait\CustomerTrait;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use App\Filament\Resources\Core\OrderResource\RelationManagers;
use App\Models\Core\Address;
use App\Models\Core\Customer;
use Filament\Forms\Components\Toggle;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedSubBuilder;

class OrderResource extends Resource
{
    use GenerateFieldsTrait, ProductTrait, CustomerTrait;

    protected static ?string $model = Order::class;
    protected static $values = [];
    public static $declinationPrices = [];

    // protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';


    public static function getNavigationGroup() : string {
        return __("Orders");
    }

    public static function getNavigationLabel() : string{
        return __("Orders");
    }

    public static function getCleanOptionString(Model $model): string
    {
        return Purify::clean(
            view('forms.components.select-image')
                ->with('name', $model?->name)
                ->with('image', $model?->productCover())
                ->render()
        );
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
                Select::make('status_id')
                    ->relationship(name :'status', titleAttribute:'name')
                    ->default(1)
                    ->required(),
                Section::make()
                    ->schema([
                        Repeater::make("orderProducts")
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                ->relationship(name:'product', titleAttribute:'name')
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => static::getCleanOptionString($record))
                                ->label(__("Product"))
                                ->required()
                                ->preload()
                                ->searchable()
                                ->allowHtml()
                                ->getSearchResultsUsing(function (string $search) {
                                    $product = Product::where('name', 'like', "%{$search}%")->limit(50)->get();

                                    return $product->mapWithKeys(function ($product) {
                                        return [$product->getKey() => static::getCleanOptionString($product)];
                                    })->toArray();
                                })->getOptionLabelUsing(function ($value): string {
                                    $product = Product::find($value);

                                    return static::getCleanOptionString($product);
                                })
                                ->createOptionForm([
                                    self::prod(),
                                ])
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                    $product = Product::find($get('product_id'));
                                    if($product != null){
                                        $discount = self::productDiscount($product);
                                        $set("discount", $discount);

                                        if(self::hasDeclinations(Product::find($get('product_id')))){
                                            self::updateFieldsDeclination($product, $set);
                                        }
                                        self::updateSubTotal($get, $set);
                                    }

                                    $set("delivery_id", null);
                                })
                                ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $state, $record) {
                                    $product = Product::find($get('product_id'));
                                        if($product != null && self::hasDeclinations(Product::find($get('product_id')))){
                                            self::updateFieldsDeclination($product, $set, $record);
                                        }
                                        self::updateSubTotal($get, $set);
                                }),
                                TextInput::make("quantity")
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->default(1)
                                    ->afterStateUpdated(function(Get $get, Set $set){
                                        self::updateSubTotal($get, $set);
                                    }),
                                    TextInput::make("discount")
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->prefix("$")
                                    ->default(0),
                                TextInput::make("sub_totals")
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->prefix("$")
                                    ->default(0)
                                    ->afterStateUpdated(function(Get $get, Set $set, $livewire){
                                        self::updateTotals($get, $set, $livewire);
                                    }),
                                    Forms\Components\Select::make('delivery_id')
                                    ->hidden(fn(callable $get) => !self::hasDelivery(Product::find($get("product_id"))))
                                    ->options(function(callable $get){
                                        $product = Product::find($get("product_id"));
                                        $options = [];
                                        if($product != null){
                                            if(self::hasDelivery($product)){
                                                foreach($product->deliveryProducts as $deliveryProduct){
                                                    $dp = $deliveryProduct->delivery; $carrier_name = "";
                                                    ($dp->carrier == null) ?: $carrier_name = "Carrier : " . $dp->carrier->carrier_name .", ";
                                                    $options[$dp->id] = $carrier_name . "price : " . number_format($dp->costs, 2, '.', '');
                                                }
                                            }

                                            return $options;
                                        }
                                    })
                                    ->lazy()
                                    ->afterStateUpdated(function(callable $get, callable $set, $livewire){
                                        self::updateTotals($get, $set, $livewire);
                                        self::updateSubTotal($get, $set);
                                    })
                                    ->required(),
                                Hidden::make("declination_id"),
                                Grid::make()
                                    ->schema([
                                        Fieldset::make("declinations")
                                        ->schema(function(Get $get, $state, Set $set, $livewire){
                                                $fields = $get("declination") ?? [];
                                                return self::GenerateFields($fields, [$state, $get, $set, $livewire]);
                                        })->label("Declination")
                                        ->columns(2)

                                    ])
                                    ->hidden(fn(Get $get) =>
                                       !self::hasDeclinations(Product::find($get('product_id')))
                                    )
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->minItems(1)
                            ->live(onBlur:true)
                            ->reactive()
                            ->reorderable(true)
                            ->reorderableWithButtons()
                            ->afterStateUpdated(function (Get $get, Set $set, $livewire, $state) {
                                // self::updateDeclinationsPrice($get, $set, $livewire);
                                self::updateTotals($get, $set, $livewire);
                            })
                           ->itemLabel(fn (array $state): ?string =>
                               self::labelProduct(self::getProduct($state['product_id'])) ?? null)
                            ->addActionLabel(__("Add products"))
                    ])->columns(1),

                Section::make()
                    ->schema([
                        Toggle::make("has_delivery")
                        ->label("has Delivery")
                        ->lazy()
                        ->afterStateUpdated(fn(Get $get, Set $set) =>
                            $set("delivery.has_delivery", $get("has_delivery")))
                        ->columnSpanFull(),
                        Fieldset::make()
                            ->relationship("delivery")
                            ->hidden(fn(Get $get) => !$get("has_delivery"))
                            ->schema([

                                Grid::make()
                                    ->schema([
                                        Hidden::make("has_delivery")
                                        ->label("has Delivery"),
                                    Grid::make()
                                        ->schema([
                                            Toggle::make("use_customer_address")
                                                ->label("Use customer address")
                                                ->live(),
                                            TextInput::make('costs')
                                                    ->label(__("Price"))
                                                    ->prefix('$')
                                                    ->default(0)
                                                    ->numeric()
                                                    ->lazy()
                                                    ->required(),

                                            DateTimePicker::make('delivery_date')
                                                    ->live()
                                                    ->withoutTime()
                                                    ->rule('after:now')
                                                    ->label(__("Delivery Date")),
                                            Select::make("carrier_id")
                                                    ->relationship(name:"carrier", titleAttribute:"carrier_name")
                                                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->carrier_name}")
                                                    ->label(__("Carrier"))
                                                    ->preload()
                                                    ->searchable()
                                        ])->columns(1)
                                        ->columnSpan(1),
                                    Fieldset::make("Address")
                                        // ->relationship("address", "id")
                                        ->schema([
                                            Select::make("country_id")
                                                ->label("Country")
                                                ->options(fn() => Country::all()->pluck("name", "id"))
                                                ->preload()
                                                ->searchable()
                                                ->live()
                                                ->required()
                                                ->afterStateUpdated(fn(callable $set) => $set("state_id", null)),
                                            Select::make("state_id")
                                                ->label("State")
                                                ->options(fn(callable $get) => State::where("country_id", $get("country_id"))->pluck("name", "id")->toArray())
                                                ->live()
                                                ->required()
                                                ->searchable()
                                                ->afterStateUpdated(fn(callable $set) => $set("city_id", null)),
                                            Select::make("city_id")
                                                ->options(fn(callable $get) => City::where("state_id", $get("state_id"))->pluck("name", "id")->toArray())
                                                ->live()
                                                ->required()
                                                ->searchable(),
                                            TextInput::make("address1")
                                                ->label("Address 1"),
                                            TextInput::make("phone")
                                                ->label("phone"),
                                            TextInput::make("email")
                                                ->label("Email"),
                                            Hidden::make("address_id"),

                                        ])
                                        ->hidden(function($livewire, $get){
                                            $hidden = false;
                                            if($get("use_customer_address")
                                            && self::hasCustomerAddress(Customer::find($livewire->data["customer_id"]))){
                                                $hidden = true;
                                            }
                                            return $hidden;
                                        })
                                        ->columns(2)
                                        ->columnSpan(1)
                                        ->afterStateHydrated(function($record, $set){
                                            if($record){
                                                if($record->address_id != null){
                                                    $address = Address::find($record->address_id)->toArray();
                                                    foreach($address as $key => $value){
                                                        if($key != "id"){
                                                            $set($key, $value);
                                                        }else{
                                                            $set("address_id", $value);
                                                        }
                                                    }
                                                }
                                            }
                                        })

                                ])

                            ])->afterStateUpdated(function(Get $get, Set $set, $livewire){
                                self::updateTotals($get, $set, $livewire);
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function(array $data, $livewire) : array{
                                    if($data["has_delivery"] === true){
                                         $costs = $data["costs"];
                                        unset($data["costs"]);

                                        $delivery_date = $data["delivery_date"];
                                        unset($data["delivery_date"]);

                                        $carrier_id = $data["carrier_id"];
                                        unset($data["carrier_id"]);

                                        $use = $data["use_customer_address"];
                                        unset($data["use_customer_address"]);

                                    $hasCustomer = self::hasCustomerAddress(Customer::find($livewire->data["customer_id"]));
                                    $address_id = null;

                                    ($hasCustomer && $use) ? $address_id = Customer::find($livewire->data["customer_id"])->addressCustomers->first()->address_id : $address_id = Address::createOrFirst($data)->id;
                                    $delivery = [
                                        "delivery_date" => $delivery_date,
                                        "costs" => $costs,
                                        "address_id" => $address_id,
                                        "carrier_id" => $carrier_id
                                    ];

                                    unset($data);

                                    return $delivery;
                                }
                                return [];
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function(array $data): array{
                                    if($data["has_delivery"] === true){
                                        $address_id = isset($data["address_id"]) ? $data["address_id"] : null;

                                        $costs = $data["costs"];
                                        unset($data["costs"]);

                                        $delivery_date = $data["delivery_date"];
                                        unset($data["delivery_date"]);

                                        $carrier_id = $data["carrier_id"];
                                        unset($data["carrier_id"]);

                                    $address = Address::find($address_id);
                                    if($address){
                                        Address::find($address_id)->update($data);
                                    }

                                    $delivery = [
                                        "delivery_date" => $delivery_date,
                                        "costs" => $costs,
                                        "address_id" => $address_id,
                                        "carrier_id" => $carrier_id
                                    ];
                                    unset($data);
                                return $delivery;
                              }

                              return [];
                            })->columns(2)
                    ]),
                Section::make()
                  ->schema([
                    Forms\Components\Grid::make()
                    ->schema(self::couponColumn())
                    ->columnSpanFull(),
                    TextInput::make('total_discount')
                          ->label(__("Discount"))
                          ->prefix('$')
                          ->disabled()
                          ->dehydrated(true)
                          ->default(0),
                      TextInput::make('total_amount_order')
                          ->label(__("Totals"))
                          ->readOnly()
                          ->prefix('$')
                          ->default(0)
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
                          ->dehydrated(true)
                          ->numeric(),
                  ])->columns(3),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.fullname')
                    ->label(__("Full name"))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(function($record){
                        if(self::hasCustomerAddress($record->customer)){
                            $addressCustomer = $record->customer->addressCustomers->first()->address;
                            return $addressCustomer->phone_code . " " . $addressCustomer->phone;
                        }
                    })
                    ->sortable(['customer.firstname', 'customer.lastname']),
                Tables\Columns\TextColumn::make('delivery.address.country.name')
                    ->label(__("Shipping Address"))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->description(function($record){
                        if($record->delivery){
                            $delivery = $record->delivery;
                            if ($region = $delivery->address) {
                                $state = $region->state->name ?? "";
                                $city = $region->city->name ?? "";
                                return "{$state}, {$city}, {$region->postal_code}";
                            }
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('reference_order')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('merchant.firstname')
                    ->numeric()
                    ->label(__("Merchant"))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('secure_key')
                    ->limit(32)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
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
                Tables\Actions\Action::make('print')
                    ->tooltip(trans('filament-ecommerce::messages.orders.actions.print'))
                    ->icon('heroicon-s-printer')
                    ->openUrlInNewTab(true)
                    ->url(fn($record) => route('invoice.show', Order::find($record->id)))
                    ->iconButton(),
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

    // public static function updateDeclinationsPrice(Get $get, Set $set, $livewire){

    //     $orderProducts = $get('orderProducts');
    //     $declinationPrices = collect($orderProducts)->map(function ($item) {
    //         $declination = Declination::find($item['declination_id'] ?? null);
    //         return $declination ? $declination->price : 0;
    //     })->toArray();

    //     $livewire->declinationPrices = $declinationPrices;
    // }
    private static function prod(){
       return
            Grid::make()
            ->schema([
                    Grid::make()
                        ->schema([
                        TextInput::make('name')
                            ->required()
                            ->lazy()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('slug', Product::createUniqueSlug($get('name')));
                            })
                            ->maxLength(255),
                            TextInput::make('slug')
                            ->live()
                            ->required()
                            ->maxLength(255)
                            ,
                    ])->columns(2),

                    ProductDetails::form(),
            ]);
    }

    public static function updateTotals(Get $get, Set $set, $livewire = null): float
    {

        $selectedProducts = collect($get('orderProducts'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
        $declinationPricesSum = array_sum(self::$declinationPrices ?? []);
        $costs = $get("delivery.costs");
        // dd(self::$declinationPrices);

        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        $subtotal = $selectedProducts->reduce(function ($acc, $product) use ($prices) {
            $productId = $product["product_id"];
            $productModel = Product::find($productId);
            $quantity = $product["quantity"];
            $productPrice = $prices[$productId];
            $productDiscount = self::productDiscount($productModel);

            // Calculate the subtotal and discount total
            $acc['total'] += ($productPrice - $productDiscount) * $quantity;
            $acc['discountTotal'] += $productDiscount * $quantity;
            $acc['deliveryTotal'] += self::productDeliveryCosts(Delivery::find($product["delivery_id"]));
            return $acc;
        }, ['total' => 0, 'discountTotal' => 0, 'deliveryTotal' => 0]);

        $total = $subtotal["total"] + ($subtotal["total"] * ($get('taxes') / 100));

        // Discount if coupon valid
        $code = $get("coupon");
        (!is_null($code)) ?: $code = "";

        $getCouponDiscount = (new Coupons())
            ->products($selectedProducts->all())
            ->discount(code : $code, total : $total);


        $set('total_amount_order', number_format(($total - $getCouponDiscount) + $subtotal["deliveryTotal"] + $costs, 2, '.', ''));
        $set("total_discount", number_format($subtotal["discountTotal"] + $getCouponDiscount, 2, '.', ''));
        if(!empty(self::$declinationPrices)){
            $set('total_amount_order', number_format(($total - $getCouponDiscount) + $subtotal["deliveryTotal"] + $costs + $declinationPricesSum, 2, '.', ''));
        }
        self::updateBalance($get, $set);

        return $total;
    }

    public static function updateSubTotal($get, $set){
        if(!is_null(Product::find($get('product_id')))){
            $product = Product::find($get('product_id'));
            $discount = self::productDiscount($product);
            $delivery_price = self::productDeliveryCosts(Delivery::find($get("delivery_id")));
            $declinationPrice = Declination::find($get("declination_id"))->price ?? 0;

            $set("sub_totals", ($product->price+$declinationPrice+$delivery_price-$discount)*$get("quantity"));
        }

    }
    private static function updateBalance(Get $get, Set $set){
        $totals = $get("total_amount_order");
        $amount = $get("order_amount");
        $set('balance', number_format($totals - $amount, 2, '.', ''));
    }

    public static function labelProduct(?Model $record){
        if($record == null){
            return;
        }
        $price = number_format($record->price, 2, '.', '');
        return "{$record->name} (\${$price})";
    }

    public static function getProduct($id){
       return Product::all()->where("id", $id)->first();
    }


    public static function afterStateUpdated($stateNatif, $state, $get, $set, $livewire = null){
        if($get("product_id") != null){
            $product = Product::find($get('product_id'));
            if(self::hasDeclinations($product)){
                $options = [];
                foreach($product->declinations as $pd){
                    foreach($pd->values as $p){
                        $attribute = $p->attributeValue->first()->attribute;
                        self::attributesExist($options, $attribute) ?: $options[$attribute->id][] = $attribute;
                    }

                }

                $valuesId = array_map(function($option) use($state){
                        if(isset($state[$option[0]->name])){
                            return $state[$option[0]->name];
                        }
                }, $options);

                $product2 = Product::with("declinations.values")->find($product->id);
                $declinations = $product2->declinations->filter(function($declination) use ($valuesId){
                        $declinationTagId = $declination->values->pluck("id")->toArray();
                        return !array_diff($valuesId, $declinationTagId);
                });

                if(is_null($stateNatif)){
                    $set("declination_id", null);
                    self::updateSubTotal($get, $set);

                }

                if(!is_null($declinations->first())){
                    $declination = $declinations->first();
                    $set("declination_id", $declination->id);
                    foreach($livewire->data["orderProducts"] as $key => $value){
                        if($state["product_id"] == $value["product_id"]){
                            $livewire->data["orderProducts"][$key] += ["declination_price" => $declination->price];
                            self::$declinationPrices[$key] = $declination->price;

                        }
                    }
                    self::updateTotals($get, $set, $livewire);
                    self::updateSubTotal($get, $set);
                }else{
                    $set("declination_id", null);
                    self::updateSubTotal($get, $set);
                }
            }
        }


    }
    public static function updateFieldsDeclination($product, $set, $record = null){
        $options = [];
        $i=0;
        foreach($product->declinations as $pd){
            foreach($pd->values as $p){$i++;
                $attribute = $p->attributeValue->first()->attribute;
                self::attributesExist($options, $attribute) ?  $options[$attribute->id][] = $p : $options[$attribute->id][] = $p;
            }

        }
        $fields = [];
        foreach($options as $key => $value){
                $values = [];
               foreach($value as $val){
                    if(!in_array($val->id, $values)){
                        $values[$val->id] = $val->value;
                    }
               }
                $fields[] = [
                    "type" => "select",
                    "name" => Attribute::find($key)->name,
                    "label" => Attribute::find($key)->name,
                    "options" => $values,
                    "lazy" => true,
                    "dehydrated"=>false,
                    "required" => true,
                    "default"=> self::getDeclinationRecord($record, $key),
                    'callback' => ["afterStateUpdated" =>[]],
                ];
        }

        $set("declination", $fields);
    }
    public static function getDeclinationRecord($record = null, $key){
        $result = null;
        if(!is_null($record)){
            $declinationRecord = $record->declination_id;
            if(!is_null($declinationRecord)){
            $values = Declination::all()->find($declinationRecord)->values;

                if(count($values) > 0){
                    foreach ($values as $value) {
                        if($value->attributeValue->first()->attribute->id == $key){
                            $result = $value->id;
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function couponColumn() : array{
        return [
            Forms\Components\Hidden::make('coupon_id'),
            Forms\Components\TextInput::make('coupon')
                ->label(trans('Coupon'))
                ->dehydrated(false)
                ->hidden(fn($record) => $record)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('apply')
                        ->tooltip(trans('filament-ecommerce::messages.orders.actions.apply'))
                        ->icon('heroicon-s-check')
                        ->action(function (Forms\Get $get,Forms\Set $set, $livewire){
                            $coupon = Coupon::query()->where('code', $get('coupon'))->first();
                            if($coupon){
                                $total =0;
                                $discount=0;
                                $items = $get('orderProducts');

                                $productIds = [];
                                foreach ($items as $orderItem){
                                    $productIds[] = $orderItem['product_id'];
                                    $discount += self::productDiscount(Product::find($orderItem['product_id']));

                                }
                                $total = self::updateTotals($get, $set, $livewire);

                                $getCouponDiscount = (new Coupons())
                                                    ->products($productIds)
                                                    ->discount(code : $get("coupon"), total : $total);
                                if($getCouponDiscount){
                                    $discount += $getCouponDiscount;

                                    $set("total_discount", $discount);
                                    $set("coupon_id", $coupon->id);
                                    self::updateTotals($get, $set, $livewire);

                                    Notification::make()
                                    ->title(trans('Coupons appliquer'))
                                    ->success()
                                    ->send();
                                }else{
                                    Notification::make()
                                    ->title("Coupon non valid")
                                    ->danger()
                                    ->send();
                                }
                            }else{
                                Notification::make()
                                    ->title("Ce coupon n'existe pas")
                                    ->danger()
                                    ->send();
                            }

                        })
                )->columnSpanFull(),
            ];
    }



}
