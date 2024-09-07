<?php

namespace App\Filament\Resources\Core\ProductResource\RelationManagers;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Admin\Delivery;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Concerns\InteractsWithRelationshipTable;

class DeliveryProductRelationManager extends RelationManager
{
    use FillTableToManyTrait;
    protected static string $relationship = 'DeliveryProducts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
              self::shipping()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('delivery.width')
                    ->label("width"),
                Tables\Columns\TextColumn::make('delivery.heigth')
                    ->label("heigth"),
                Tables\Columns\TextColumn::make('delivery.weigth')
                    ->label("Weigth"),
                Tables\Columns\TextColumn::make('delivery.depth')
                    ->label("Depth"),
                Tables\Columns\TextColumn::make('delivery.costs')
                    ->label("Costs"),
                Tables\Columns\TextColumn::make('delivery.carrier.carrier_name')
                    ->label("Delivery mode"),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ,

            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function shipping(){
        return        
                    Grid::make()
                    ->label(__("Delivery"))
                        ->schema([
                            TextInput::make("delivery.width")
                                ->numeric(),
                            TextInput::make("delivery.heigth")
                                ->numeric(),
                            TextInput::make("delivery.depth")
                                ->numeric(),
                            TextInput::make("delivery.weigth")
                                ->numeric(),
                            TextInput::make("delivery.costs")
                                ->numeric(),
                            Select::make("carrier_id")
                            ->relationship(name:"delivery.carrier", titleAttribute:"carrier_name")
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->carrier_name}")
                            ->label(__("Carrier"))
                            ->preload()
                            ->searchable()
                        ])->columns(2);
        }

        protected function configureCreateAction(Tables\Actions\CreateAction $action): void
        {
            parent::configureCreateAction($action);
            $action->mutateFormDataUsing(function ($data) {   
                return self::processFillTable($data["delivery"], Delivery::class, "delivery_id");

            });
    
        }
       


}
