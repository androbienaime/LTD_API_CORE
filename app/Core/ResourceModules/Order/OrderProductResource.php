<?php

namespace App\Core\ResourceModules\Order;

use App\Core\ResourceModules\Concerns\HasOrderTotal;
use App\Core\ResourceModules\Product\ProductDetails;
use App\Core\Trait\GenerateFieldsTrait;
use App\Core\Trait\ProductTrait;
use App\Filament\Resources\Core\ProductResource;
use App\Models\Core\Attribute;
use App\Models\Core\Currency;
use App\Models\Core\Declination;
use App\Models\Core\Product;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

class OrderProductResource
{
    use HasOrderTotal, ProductTrait, GenerateFieldsTrait;

    public static $declinationPrices = 0; // Stores prices for product variations

    /**
     * Returns clean HTML for select options with product image and name
     */
    public static function getCleanOptionString(Model $model): string
    {
        return Purify::clean(
            view('forms.components.select-image')
                ->with('name', $model?->name)
                ->with('image', $model?->productCover())
                ->render()
        );
    }

    /**
     * @return array
     */
    public static function form() : array
    {
        return [
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
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $product = Product::find($get('product_id'));
                                if($product != null){
                                    $discount = self::productDiscount($product);

                                    $to = Currency::find($get('../../currency_id'));


                                    $set("discount", Currency::convert($discount, $product->currency->iso_code, $to->iso_code));

                                    if(Product::hasDeclinations(Product::find($get('product_id')))){
                                        self::updateFieldsDeclination($product, $set, $get);
                                    }
                                    self::updateSubTotal($get, $set);
                                }

                                $set("delivery_id", null);
                            })
                            ->afterStateHydrated(function (Get $get, Set $set, $state, $record) {
                                $product = Product::find($get('product_id'));
                                if($product != null && Product::hasDeclinations(Product::find($get('product_id')))){
                                    self::updateFieldsDeclination($product, $set, $get, $record);
                                }
                                // self::updateSubTotal($get, $set);
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
                            ->reactive()
                            ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("../../currency_id"))->first()->symbol)
                            ->default(0),
                        TextInput::make("sub_totals")
                            ->numeric()
                            // ->disabled()
                            ->hint(function(callable $get, Set $set){
                                if($product = Product::find($get('product_id'))){
                                    return "{$product->currency->symbol} ". self::updateSubTotal($get, $set);
                                }
                            })
                            // ->dehydrated(true)
                            ->reactive()
                            ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("../../currency_id"))->first()->symbol)
                            ->default(0)
                            ->afterStateUpdated(function(Get $get, Set $set, $livewire){
                                self::updateTotals($get, $set, $livewire);
                            }),
                        Select::make('delivery_id')
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
                        Hidden::make("declination_id")
                        ->afterStateUpdated(function(Get $get, Set $set) {
                            if ($get('declination_id')) {
                                self::updateTotals($get, $set);
                            }
                        })->reactive(),
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
                            !Product::hasDeclinations(Product::find($get('product_id')))
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
                    ->itemLabel(function (array $state){
                        $product = Product::find($state['product_id'])?->first() ?? null;
                        return self::labelProduct($product);
                    })
                    ->addActionLabel(__("Add products"))
                    ->extraItemActions([
                        Action::make('openProduct')
                            ->tooltip('Open product')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->url(function (array $arguments, Repeater $component): ?string {
                                $itemData = $component->getRawItemState($arguments['item']);

                                $product = Product::find($itemData['product_id']);

                                if (! $product) {
                                    return null;
                                }

                                return ProductResource::getUrl('edit', ['record' => $product]);
                            }, shouldOpenInNewTab: true)
                            ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
                    ])
            ];
    }

    private static function prod(): Grid
    {
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

    public static function labelProduct(?Model $record){
        if($record == null){
            return;
        }
        return "{$record->name} ";

    }

    public static function updateFieldsDeclination($product, $set, $get, $record = null): void
    {
        $options = Product::getAttributeToArray($product);

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
                "default"=> self::getDeclinationRecord($key, $record),
                'callback' => ["afterStateUpdated" =>[]],
            ];
        }

        $set("declination", $fields);
    }

    public static function hintDeclinationPrice($get, $record) : string{
        if(!is_null($declination = Declination::find($get('declination_id')))){
            return "{$record->currency->symbol} " . $declination->price;
        }
        return "";
    }
    public static function getDeclinationRecord($key, $record = null ){
        $result = null;
        if(!is_null($record)){
            $declinationRecord = $record->declination_id;
            if(!is_null($declinationRecord)){
                $values = Declination::all()->find($declinationRecord)->values;

                if(count($values) > 0){
                    foreach ($values as $value) {
                        if($value->attribute->id == $key){
                            $result = $value->id;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $stateNatif
     * @param $state
     * @param $get
     * @param $set
     * @param $livewire
     * @return void
     */
    public static function afterStateUpdated($stateNatif, $state, $get, $set, $livewire = null): void
    {
        if($get("product_id") != null){
            $product = Product::find($get('product_id'));

            if(Product::hasDeclinations($product)){
                $options = [];
                foreach($product->declinations as $pd){
                    foreach($pd->values as $p){
                        $attribute = $p->attribute;
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
}
