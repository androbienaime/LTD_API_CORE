<?php

namespace App\Core\ResourceModules\Product;

use App\Models\Core\Category;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieTagsInput;

class ProductSeo
{
    public static function form(){
        return Grid::make()
        ->schema([
            TextInput::make('slug')
                ->live()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->columnSpanFull(),

            Grid::make("ltspSeo")
                ->relationship("ltspSeo", "id")
                ->schema([
                        TextInput::make('meta_title')
                            ->live()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('meta_description')
                        ->columnSpanFull()
                        ->label(__("Meta Description"))
                        ->columnSpanFull(),

                        Select::make('is_redirection')
                            ->options(
                                [
                                    "0" => __("No"),
                                    "1" => __("Yes"),
                                ]
                            )
                            ->required()
                            ->default("0")
                            ->label("Redirection"),

                            Select::make('category_id')
                            ->label('Category')
                            ->searchable()
                            ->options(
                                Category::all()
                                ->pluck('name', 'id')
                                ->toArray()
                            ),
                        SpatieTagsInput::make('product_tags')
                            ->columnSpanFull()
                            ->label("Tags")
                            ->splitKeys(['Tab', ','])
                            ->reorderable()
                            ->nestedRecursiveRules([
                                'min:3',
                                'max:50',
                            ]),
            ])->columns(2)

        ]);
    }
}
