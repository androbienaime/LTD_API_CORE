<?php

namespace App\Core\ResourceModules\Product;

use App\Core\Trait\FillTableToManyTrait;
use App\Models\Core\Delivery;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;

class ProductShippings
{
    use FillTableToManyTrait;
    
    public static function form(){
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
}
