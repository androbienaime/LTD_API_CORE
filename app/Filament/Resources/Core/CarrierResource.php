<?php

namespace App\Filament\Resources\Core;

use App\Filament\Resources\Core\CarrierResource\Pages;
use App\Filament\Resources\Core\CarrierResource\RelationManagers;
use App\Models\Core\Carrier;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup() : string {
        return __("Shipping");
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('carrier_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('transit_time')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('speed_grade')
                    ->maxLength(255)
                    ->default(null),
                SpatieMediaLibraryFileUpload::make('product_image')
                    ->reorderable()
                    ->imageEditor()
                    ->image()
                    ->responsiveImages()
                    ->conversion('thumb')
                    ->optimize('webp')                    
                    ->panelLayout("grid")
                    ,
                Forms\Components\TextInput::make('tracking_url')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('free_shipping')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('carrier_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transit_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('speed_grade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracking_url')
                    ->searchable(),
                Tables\Columns\IconColumn::make('free_shipping')
                    ->boolean(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarriers::route('/'),
            'view' => Pages\ViewCarrier::route('/{record}'),
        ];
    }
}
