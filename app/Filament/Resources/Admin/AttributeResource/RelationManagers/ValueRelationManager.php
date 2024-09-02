<?php

namespace App\Filament\Resources\Admin\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Admin\Value;
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
    use FillTableToManyTrait;

    protected static string $relationship = 'attributeValue';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('value.value')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),
                Grid::make()
                ->schema([
                        Forms\Components\TextInput::make('value.url')
                        ->maxLength(255),
                        Forms\Components\TextInput::make('value.meta_title')
                        ->label(__("Meta Title"))
                        ->maxLength(255),
                        Forms\Components\Toggle::make('value.indexable'),
                        Forms\Components\ColorPicker::make('value.color')
                        ->hidden($this->getOwnerRecord()->type != "color"),
                ])->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value.value')
                ->label("value"),
                Tables\Columns\TextColumn::make('value.url')
                ->label("url"),
                ColorColumn::make('value.color')
                ->label("color"),
                ToggleColumn::make('value.indexable')
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

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
        {
            parent::configureCreateAction($action);
            $action->mutateFormDataUsing(function ($data) {   
                return self::processFillTable($data["value"], Value::class, "value_id");

            });
    
        }

        protected function configureSaveAction(Tables\Actions\EditAction $action): void
        {
            parent::configureEditAction($action);
            $action->mutateFormDataUsing(function ($data) {   
                dd($data);
                return self::processFillTable($data["value"], Value::class, "value_id");

            });
    
        }

}
