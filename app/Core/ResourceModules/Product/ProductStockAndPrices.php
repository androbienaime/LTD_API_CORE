<?php

namespace App\Core\ResourceModules\Product;

use App\Core\Trait\ManageStockTrait;
use Filament\Forms\Get;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class ProductStockAndPrices
{
    use ManageStockTrait;

    public static function form(){
        return Grid::make()
        ->schema([
            Fieldset::make()
            ->label(__("Gestion Stock"))
                ->schema([
                Grid::make()
                ->schema([
                    Toggle::make('is_in_stock')
                        ->label(__("In Stock"))
                        ->live()
                        ->default(true)
                        ->required(),
                    Toggle::make('has_unlimited_stock')
                        ->label("Unlimited Stock")
                        ->live()
                        ->hidden(fn (Get $get) => !$get('is_in_stock'))
                        ->default(true)
                        ->required(),
                    Toggle::make('has_stock_alert')
                        ->label(__("Stock Alert"))
                        ->live()
                        ->hidden(fn (Get $get) => !$get('is_in_stock'))
                        ->default(false)
                        ->required(),

                ])->columns(3),
                Grid::make()
                ->schema([
                    TextInput::make('stock_quantity')
                    ->live()
                    ->hidden(fn (Get $get) => !$get('is_in_stock') || $get('has_unlimited_stock'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->columnSpanFull()
                    ->required(),

                    TextInput::make('min_stock_alert')
                    ->live()
                    ->hidden(fn (Get $get) => !$get('is_in_stock') || !$get('has_stock_alert'))
                    ->minValue(0)
                    ->lt("max_stock_alert")
                    ->required(fn(callable $get) => $get("max_stock_alert") == null)
                    ->numeric()
                    ->default(0),
                    TextInput::make('max_stock_alert')
                    ->live()
                    ->minValue(0)
                    ->gt('min_stock_alert')
                    ->required(fn(callable $get) => $get("min_stock_alert") == null)
                    ->hidden(fn (Get $get) => !$get('is_in_stock') || !$get('has_stock_alert'))
                    ->numeric()
                    ->default(0),

                ])->columns(2)


                ]),
                Fieldset::make()
                ->label(__("Price"))
                    ->schema([
                    Grid::make()
                    ->schema([
                        Toggle::make('has_multi_price')
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
                    Toggle::make('has_discount')
                    ->live(),
                    Grid::make()
                    ->relationship("productDiscount", "id")
                    ->live()
                    ->hidden(fn (Get $get) => !$get('has_discount'))
                    ->schema([
                        TextInput::make("discount")
                        ->live()
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->label(__("Discount"))
                        ->prefix("%")
                        ->columnSpanFull(),
                        Grid::make()
                            ->schema([
                                DateTimePicker::make('start_date')
                                ->live()
                                ->beforeOrEqual("end_date")
                                ->after("yesterday")
                                ->label(__("Start Date")),
                                DateTimePicker::make('end_date')
                                ->live()
                                ->rule('after:now')
                                ->afterOrEqual("start_date")
                                ->label(__("End Date")),
                            ])->columns(2)
                    ])
                ])

        ]);
    }
}
