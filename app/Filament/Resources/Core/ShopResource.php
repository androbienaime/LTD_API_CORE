<?php

namespace App\Filament\Resources\Core;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Core\Shop;
use Filament\Tables\Table;
use App\Models\Core\Category;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Core\ShopResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\Core\ShopResource\RelationManagers;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup() : string {
        return __("Shops");
    }

    public static function getNavigationLabel() : string{
        return __("Shops");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('shop_cover')
                                    ->imageEditor()
                                    ->image()
                                    ->collection('shop_cover')
                                    ->responsiveImages()
                                    ->conversion('thumb')
                                    ->optimize('webp')
                                    ->columnSpan('full')
                                    ->downloadable()
                                    // ->imagePreviewHeight(150)
                                    ->panelLayout("compact")
                                    ,
                        SpatieMediaLibraryFileUpload::make('shop_profile')
                                ->avatar()
                                ->collection('shop_profile')
                                ->label("")
                                ->imageEditor()
                                ->circleCropper()
                                ->responsiveImages()
                                ->conversion('thumb')
                                ->optimize('webp')
                                ->columnSpan('full')
                                ->downloadable()
                                ->panelAspectRatio('8:8')
                                // ->imagePreviewHeight(150)
                                ->panelLayout("circle")
                                ->alignCenter()
                                ,
                    ])->extraAttributes(['class' => 'custom-section']),

                Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('slug', Shop::createUniqueSlug($get('name')));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->live()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('types')
                            ->options([
                                "Store" => "Store",
                                "Dry" => "Dry",
                                "Market" => "Market",
                                "Restaurant" => "Restaurant",
                                "Bar" => "Bar",
                                "Cafe" => "Cafe",
                                "Other" => "Other",
                            ])
                            ->searchable()
                            ->preload(),
                        Select::make('categories')
                                ->relationship('categories')
                                ->multiple()
                                ->searchable()
                                ->options(Category::all()
                                    ->pluck('name', 'id')
                                    ->toArray()
                                )
                                ->label(__("Categories")),

                        Forms\Components\RichEditor::make('shop_description')
                            ->maxLength(255)
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'redo',
                                'underline',
                                'undo',])
                            ->ColumnSpan("full"),

                        Forms\Components\TextInput::make('theme_name')
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('theme_color'),
                        Forms\Components\Toggle::make('status')
                            ->required(),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_color')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
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
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'view' => Pages\ViewShop::route('/{record}'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
