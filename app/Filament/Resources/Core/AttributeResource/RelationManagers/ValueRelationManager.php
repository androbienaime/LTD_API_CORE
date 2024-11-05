<?php

namespace App\Filament\Resources\Core\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Core\Value;
use Filament\Forms\Components\Grid;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\BooleanColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ValueRelationManager extends RelationManager
{

    protected static string $relationship = 'values';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('value')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),
                Grid::make()
                ->schema([
                        Forms\Components\TextInput::make('url')
                        ->maxLength(255),
                        Forms\Components\TextInput::make('meta_title')
                        ->label(__("Meta Title"))
                        ->maxLength(255),
                        Forms\Components\Toggle::make('indexable'),
                        Forms\Components\ColorPicker::make('color')
                        ->hidden($this->getOwnerRecord()->type != "color"),
                ])->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('values')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                ->label("value"),
                Tables\Columns\TextColumn::make('url')
                ->label("url"),
                ColorColumn::make('color')
                ->label("color"),
                ToggleColumn::make('indexable')
                ->label("indexable")


            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
