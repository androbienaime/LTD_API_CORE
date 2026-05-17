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
use App\Models\Location\City;
use App\Models\Location\State;
use App\Models\Location\Country;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\Core\ShopResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\Core\ShopResource\Pages\EditShop;
use App\Filament\Resources\Core\ShopResource\Pages\ViewShop;
use App\Filament\Resources\Core\ShopResource\Pages\ListShops;
use App\Filament\Resources\Core\ShopResource\Pages\CreateShop;
use App\Filament\Resources\Core\ShopResource\RelationManagers;
use App\Services\ShopMembershipService;
use Spatie\Permission\Models\Role;

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
                            ->live(onBlur: true)
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

                        

                    Section::make()
                    ->schema([
                        Fieldset::make("Theme")
                            ->schema([
                                Forms\Components\TextInput::make('theme_name')
                                    ->maxLength(255),
                                Forms\Components\ColorPicker::make('theme_color'),
                            ])->columns(1)
                            ->columnSpan(1),
                        Fieldset::make("address")
                            ->relationship("address", "id")
                            ->schema([
                                Select::make("country_id")
                                ->label("Country")
                                ->options(fn() => Country::all()->pluck("name", "id"))
                                ->preload()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set("state_id", null)),
                                Select::make("state_id")
                                    ->label("State")
                                    ->options(fn(callable $get) => State::where("country_id", $get("country_id"))->pluck("name", "id")->toArray())
                                    ->live()
                                    ->searchable()
                                    ->afterStateUpdated(fn(callable $set) => $set("city_id", null)),
                                Select::make("city_id")
                                    ->options(fn(callable $get) => City::where("state_id", $get("state_id"))->pluck("name", "id")->toArray())
                                    ->live()
                                    ->searchable(),
                                TextInput::make("address1")
                                    ->label("Address 1"),
                                TextInput::make("phone")
                                    ->label("phone"),
                                TextInput::make("email")
                                    ->label("Email"),
                            ])->columns(2)
                            ->columnSpan(1)
                    ])->columns(2),
                    
                 Section::make()
                    ->schema([
                        Repeater::make('accountShop')
                            ->relationship()
                            ->label(__("Accounts And Roles"))
                            ->schema([
                                Select::make('account_id')
                                    ->relationship("account", "email")
                                    ->label(__("Account"))
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record) => $record->firstname . " (" . $record->email . ")"
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(), // ← Obligatoire dans chaque ligne

                                Select::make('role_id')
                                    ->label(__('Role'))
                                    ->options(function () {
                                        return Role::query()
                                            ->where('guard_name', 'account')
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            // ← Si l'utilisateur connecté n'est PAS un account,
                            //   au moins 1 entrée est obligatoire
                            ->minItems(fn () => auth('account')->check() ? 0 : 1)
                            ->default([])
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($state, $set, $record) {
                                if (! $record) return;

                                $members = $record->accounts()
                                    ->get()
                                    ->map(fn ($account) => [
                                        'account_id' => $account->id,
                                        'role_id'    => $account->pivot->role_id,
                                    ])
                                    ->toArray();

                                $set('memberships', $members);
                            })
                            ->columns(2)
                            ->columnSpan("full"),
                    ]),
                    
                    Forms\Components\Toggle::make('status')
                        ->required()
                        ->default(true),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('shop_profile')
                    ->label(__("Profile"))
                    ->collection("shop_profile")
                    ->circular()
                    ->conversion('thumb')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn(Shop $record): string => $record?->address?->email ?? "")
                    ->searchable(),
                    TextColumn::make('description')
                    ->limit(35)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                 
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                 
                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                    Tables\Columns\TextColumn::make('slug')
                    ->label(__("Preview"))
                    ->formatStateUsing(static function($state){
                        return __("Preview");
                    })
                    ->url(fn($record) => $record->slug)
                    ->openUrlInNewTab()
                    ->icon("heroicon-m-arrow-top-right-on-square")
                    ->iconPosition(IconPosition::After) 
                    ->color("primary")
                    ->tooltip(fn(Model $record) => $record->slug)
                    ->searchable(),
                    TextColumn::make('types')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->searchable(),
                    TextColumn::make('categories.name')
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->badge()
                    ->separator(",")
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('fulladdress')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('reference')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_color')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('address.country.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->searchable(),
                TextColumn::make('address.state.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->searchable(),
                TextColumn::make('address.city.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->toggleable(isToggledHiddenByDefault: false),
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
                Tables\Filters\SelectFilter::make('category')
                ->relationship("categories", "name")
                ->multiple()
                ->preload()
                ->label(__("Category"))
                ->searchable()
                ->options(Category::all()
                    ->pluck('name', 'id')
                    ->toArray()
                ),
                Tables\Filters\SelectFilter::make('types')
                ->options([
                    "Store" => "Store",
                    "Dry" => "Dry",
                    "Market" => "Market",
                    "Restaurant" => "Restaurant",
                    "Bar" => "Bar",
                    "Cafe" => "Cafe",
                    "Other" => "Other",
                ]),
                Tables\Filters\TernaryFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort("updated_at", "desc");
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
