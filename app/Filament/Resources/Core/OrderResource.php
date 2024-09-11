<?php

namespace App\Filament\Resources\Core;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Core\Order;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Core\Product;
use App\Models\Core\Attribute;
use App\Models\Core\OrderStatus;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Builder\Block;
use function Symfony\Component\String\match;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
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
    protected static ?string $model = Order::class;

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
                                ->relationship(name:'product', titleAttribute:'name')
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => self::labelProduct($record))
                                ->label(__("Product"))
                                ->required()
                                ->preload()
                                ->searchable()
                                ->createOptionForm([
                                    self::prod(),
                                ])
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        $product = Product::find($get('product_id'));
                                        if(self::hasDeclinaitions(Product::find($get('product_id')))){
                                            self::updateFieldsDeclination($product, $set);
                                        }
                                }),
                                TextInput::make("quantity")
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                Grid::make()
                                    ->schema([
                                        Grid::make("dec")
                                            ->schema(function(Get $get){
                                                $fields = $get("dyc") ?? [];
                                                return self::GenerateFields($fields);

                                        })->columns(2)
    
                                    ])
                                    ->hidden(fn(Get $get) => 
                                       !self::hasDeclinaitions(Product::find($get('product_id')))
                                    )
                            ])
                            ->columns(3)
                            ->collapsed()
                            ->minItems(1)
                            ->live()
                            ->reorderable(true)
                            ->reorderableWithButtons()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
//                            ->itemLabel(fn (array $state): ?string =>
//                                self::labelProduct(self::getProduct($state['product_id'])) ?? null)
                            ->addActionLabel(__("Add products"))
                            ->addAction(fn(Action $action) =>$action->label("Hello"))
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
    
    public static function hasDeclinaitions(?Product $product){
        if($product == null){ return; } 
        $hasDeclination = false;
        if($product->product_with_declination == true && $product->declinations->count() > 0){
            $hasDeclination = true;
           //
        }

        return $hasDeclination;
    }

    public static function updateFieldsDeclination($product, $set){
        $options = [];
        foreach($product->declinations as $pd){
            foreach($pd->values as $p){
                $attribute = $p->attributeValue->first()->attribute;
                self::attributesExist($options, $attribute) ?: $options[$attribute->id][] = $p;
            }
            
        }

        $fiel = [];
        foreach($options as $key => $value){
                $fiel[] = [
                    "type" => "select",
                    "name" => Attribute::find($key)->name,
                    "label" => Attribute::find($key)->name,
                    "options" => $value,
                    "required" => true,
                ];
        }
        $fields = self::Fields();
        $set("dyc", $fiel);
        // dd($options);
    }

    public static function attributesExist($options, $attributes){
            // Parcourir chaque groupe d'options
        foreach ($options as $attributeName => $values) {
            // Parcourir les sous-valeurs du tableau (les sous-éléments)
            foreach ($values as $option) {
                // Vérifier si l'ID correspond
                if ($option->id == $attributes->id) {
                    return true; // Si trouvé, retourner immédiatement true
                }
            }
        }

        // Si aucune correspondance n'a été trouvée, retourner false
        return false;
    }

    public static function Fields(){
       return $fields = [
            [
                'type' => 'input',
                'name' => 'name',
                'label' =>'salut',
                'required' => true,
                'lazy' => true,
                // 'callback' => function (Get $get, Set $set) {
                //     $set('slug', Product::createUniqueSlug($get('name')));
                // },
                'maxLength' => 255,
            ],
            [
                'type' => 'select',
                'name' => 'slug',
                'label' => "geri",
                'live' => true,
                'required' => true,
                'maxLength' => 255,
            ],
        ];
    }

    public static function GenerateFields(array $fields){
        return array_map(function($field) {
            $input = Grid::make()->schema([]);
            

            switch($field["type"] ){
            
                case "input":{
                    $input =TextInput::make($field['name'])
                    ->required($field['required'] ?? false)
                    ->lazy($field['lazy'] ?? false)
                    ->maxLength($field['maxLength'] ?? null);
        
                    if (isset($field['live'])) {
                        $input->live($field['live']);
                    }
            
                    if (isset($field['callback'])) {
                        $input->afterStateUpdated($field['callback']);
                    }
                    break;
                }
                case "select" :{
                    $input = Select::make($field['name'])
                    ->options(array_map(function($val){
                        return [$val->id] = $val->value;
                    }, $field["options"]))
                    ->required($field['required'] ?? false)
                    ->lazy($field['lazy'] ?? false);
                }
            }
            
            return $input;
        }, $fields);
    }
}
