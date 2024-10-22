<?php

namespace App\Filament\Resources\Core;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Core\Account;
use App\Models\Core\Address;
use App\Models\Location\City;
use App\Models\Location\State;
use App\Models\Location\Country;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Core\AccountResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\Core\AccountResource\RelationManagers;
use App\Filament\Resources\Core\AccountResource\Pages\EditAccount;
use App\Filament\Resources\Core\AccountResource\Pages\ViewAccount;
use App\Filament\Resources\Core\AccountResource\Pages\ListAccounts;
use App\Filament\Resources\Core\AccountResource\Pages\CreateAccount;

class AccountResource extends Resource
{
    use FillTableToManyTrait;

    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('account_cover')
                                    ->imageEditor()
                                    ->image()
                                    ->collection('account_cover')
                                    ->responsiveImages()
                                    ->conversion('thumb')
                                    ->optimize('webp')
                                    ->columnSpan('full')
                                    ->downloadable()
                                    // ->imagePreviewHeight(150)
                                    ->panelLayout("compact")
                                    ,
                        SpatieMediaLibraryFileUpload::make('account_profile')
                                ->avatar()
                                ->collection('account_profile')
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

                Section::make() 
                    ->schema([
                        Fieldset::make('Personal Information')
                            ->schema([
                                Forms\Components\TextInput::make('firstname')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(callable $set, callable $get) => 
                                        $set("username", Account::generateUniqueUsername($get("firstname")." ".$get("lastname")))
                                    ),
                                Forms\Components\TextInput::make('lastname')
                                    ->maxLength(255)
                                    ->default(null)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(callable $set, callable $get) => 
                                        $set("username", Account::generateUniqueUsername($get("firstname")." ".$get("lastname")))
                                    ),
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label(__("Date of Birth"))
                                    ->minDate(now()->subYears(100))
                                    ->maxDate(now()->subYears(10)),
                                Forms\Components\Select::make('gender')
                                    ->options([
                                        "male" => __("Male"),
                                        "female" => __("Female")
                                    ])
                                    ->default("male"),
                            ])->columns(2),
                    ]),
                Section::make()
                    ->schema([
                        Fieldset::make('Connection Information')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255)
                                    ->default(null),
                                Forms\Components\Select::make('loginBy')
                                    ->options([
                                        "email" => __("Email"),
                                        "phone" => __("Phone")
                                    ])->columnSpanFull()
                                    ->default("email"),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->maxLength(255)
                                    ->revealable(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->password()
                                    ->label('Confirm Password')
                                    ->requiredWith('password')
                                    ->revealable(),
                            ])->columnSpan(1),

                            Repeater::make('address')
                                ->relationship('addresses')
                                ->schema([
                                    Grid::make("address_id")
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
                                ])->columnSpan(1)
                                ->mutateRelationshipDataBeforeCreateUsing(function(array $data){
                                    return self::processFillTable($data, Address::class, "address_id");
                                }),
                        ])->columns(2),

                
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_notification_active')
                    ->required(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fullname')
                    ->searchable()
                    ->label(__("Full name"))
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color("secondary"),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_login')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loginBy')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('lang')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    // ...
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                
                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }
                
                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }
                
                        return $indicators;
                    })
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
