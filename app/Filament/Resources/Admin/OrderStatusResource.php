<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\OrderStatusResource\Pages;
use App\Filament\Resources\Admin\OrderStatusResource\RelationManagers;
use App\Models\Admin\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    public static function getNavigationGroup() : string {
        return __("Orders");
    }

    public static function getNavigationLabel() : string{
        return __("Order Status");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('action')
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('color'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__("name"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label(__("action"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label(__("icon"))
                    ->searchable(),
                Tables\Columns\ColorColumn::make("color")
                    ->label(__("color"))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderStatuses::route('/'),
            'create' => Pages\CreateOrderStatus::route('/create'),
            'view' => Pages\ViewOrderStatus::route('/{record}'),
            'edit' => Pages\EditOrderStatus::route('/{record}/edit'),
        ];
    }
}
