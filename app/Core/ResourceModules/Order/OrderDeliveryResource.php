<?php

namespace App\Core\ResourceModules\Order;

use App\Core\ResourceModules\Concerns\HasOrderProductDiscount;
use App\Core\ResourceModules\Concerns\HasOrderTotal;
use App\Core\Trait\ProductTrait;
use App\Models\Core\Address;
use App\Models\Core\Currency;
use App\Models\Core\Customer;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryResource
{
    use HasOrderTotal, ProductTrait;
    public static function form(): Grid
    {
        return Grid::make()
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
                                            ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("../currency_id"))->first()->symbol)
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
                                            && Customer::hasCustomerAddress(Customer::find($livewire->data["customer_id"]))){
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

                            $hasCustomer = Customer::hasCustomerAddress(Customer::find($livewire->data["customer_id"]));
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
            ]);
    }
}
