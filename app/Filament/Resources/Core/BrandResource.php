<?php

namespace App\Filament\Resources\Core;

use App\Core\ResourceModules\HasResourceStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Core\Brand;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Core\BrandResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Filament\Resources\Core\BrandResource\RelationManagers;
use Filament\Forms\Components\ColorPicker;

class BrandResource extends Resource
{
    use HasResourceStatus;
    
    protected static ?string $model = Brand::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;


    public static function getNavigationGroup() : string {
        return __("Catalogs");
    }

    public static function getNavigationLabel() : string{
        return __("Brand");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('brand_logo')
                    ->multiple()
                    ->reorderable()
                    ->imageEditor()
                    ->responsiveImages()
                    ->conversion('thumb')
                    ->optimize('webp')
                    ->columnSpan('full')
                    ->imagePreviewHeight(50)
                    ->panelLayout("grid")
                    ,
                ColorPicker::make('color'),
                self::FormStatus()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                SpatieMediaLibraryImageColumn::make('brand_logo')
                    ->label(__("Image"))
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),

                    self::TablesStatus(),

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
                self::ActionStatus(),
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
            'index' => Pages\ListBrands::route('/'),
        ];
    }
}
