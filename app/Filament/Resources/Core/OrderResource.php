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
use App\Models\Core\Attribute;
use Filament\Facades\Filament;
use App\Core\Trait\ProductTrait;
use App\Models\Core\Declination;
use App\Models\Core\OrderStatus;
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
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Builder\Block;
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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use App\Filament\Resources\Core\OrderResource\RelationManagers;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedSubBuilder;

class OrderResource extends Resource
{
    use GenerateFieldsTrait, ProductTrait;
    
    protected static ?string $model = Order::class;
    protected static $values = [];
    // protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

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
                                ->relationship(name:'product', titleAttribute:'name', 
                                    modifyQueryUsing: fn(Builder $query) => $query->where("status", true))
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => self::labelProduct($record))
                                ->label(__("Product"))
                                ->required()
                                ->preload()
                                ->searchable()
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
                                        self::updateTotals($get, $set);
                                    }),
                                Hidden::make("declination_id"),
                                Grid::make()
                                    ->schema([
                                        Grid::make("declinations")
                                        ->schema(function(Get $get, $state, Set $set, $livewire){                                               
                                                $fields = $get("declination") ?? [];
                                                return self::GenerateFields($fields, [$state, $get, $set]);
                                                // return GenerateFields::Generate($fields, [$state, $get, $set]);
                                        })->columns(2)
    
                                    ])
                                    ->hidden(fn(Get $get) => 
                                       !self::hasDeclinations(Product::find($get('product_id')))
                                    )
                            ])
                            ->columns(4)
                            // ->collapsed()
                            ->minItems(1)
                            ->live()
                            ->reactive()
                            ->reorderable(true)
                            ->reorderableWithButtons()
                            ->afterStateUpdated(function (Get $get, Set $set, $livewire) {
                                self::updateTotals($get, $set);
                            })
                            // ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array{
                            // //    dd($data); // return self::processFillTable($data, Delivery::class, "delivery_id");
                            //  })
//                            ->itemLabel(fn (array $state): ?string =>
//                                self::labelProduct(self::getProduct($state['product_id'])) ?? null)
                            ->addActionLabel(__("Add products"))
                    ])->columns(1),


              Section::make()
                  ->schema([
                    Forms\Components\Grid::make()
                    ->schema(self::couponColumn())
                    ->columnSpanFull(),
                    TextInput::make('total_discount')
                          ->label(__("Discount"))
                          ->prefix('$')
                          ->disabled()
                          ->default(0)
                          ->required(),
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

    public static function updateTotals(Get $get, Set $set): float
    {
        $selectedProducts = collect($get('orderProducts'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
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
        
            return $acc;
        }, ['total' => 0, 'discountTotal' => 0]);
        // Update the state with the new values
       // $set('subtotal', number_format($subtotal, 2, '.', ''));
       
        $total = $subtotal["total"] + ($subtotal["total"] * ($get('taxes') / 100));
        
        // Discount if coupon valid
        $code = $get("coupon");
        (!is_null($code)) ?: $code = "";

        $getCouponDiscount = (new Coupons())
            ->products($selectedProducts->all())
            ->discount(code : $code, total : $total);
        
        $set('total_amount_order', number_format($total - $getCouponDiscount, 2, '.', ''));
        $set("total_discount", number_format($subtotal["discountTotal"] + $getCouponDiscount, 2, '.', ''));
        self::updateBalance($get, $set);

        return $total;
    }

    public static function updateSubTotal($get, $set){
        if(!is_null(Product::find($get('product_id')))){
            $product = Product::find($get('product_id'));
            $discount = self::productDiscount($product);

            $declinationPrice = Declination::find($get("declination_id"))->price ?? 0;
            $set("sub_totals", ($product->price+$declinationPrice-$discount)*$get("quantity"));
        }

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
    

    public static function afterStateUpdated($stateNatif, $state, $get, $set){
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
                    "live" => true,
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
                        ->action(function (Forms\Get $get,Forms\Set $set){
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
                                $total = self::updateTotals($get, $set);

                                $getCouponDiscount = (new Coupons())
                                                    ->products($productIds)
                                                    ->discount(code : $get("coupon"), total : $total);
                                if($getCouponDiscount){
                                    $discount += $getCouponDiscount;

                                    $set("total_discount", $discount);
                                    $set("coupon_id", $coupon->id);
                                    self::updateTotals($get, $set);
                                    
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
