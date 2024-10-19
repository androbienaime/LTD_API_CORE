<?php

namespace App\Filament\Resources\Core;

use App\Core\Trait\FillTableToManyTrait;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Core\Customer;
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
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Core\CustomerResource\Pages;
use App\Filament\Resources\Core\CustomerResource\RelationManagers;
use App\Models\Core\Address;

class CustomerResource extends Resource
{
    use FillTableToManyTrait;
    protected static ?string $model = Customer::class;
    
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup() : string {
        return __("Customers");
    }

    public static function getNavigationLabel() : string{
        return __("Customers");
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Fieldset::make("Personal Infos")
                            ->schema([
                            Forms\Components\TextInput::make('firstname')
                                ->required()
                                ->minLength(2)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('lastname')
                                ->required()
                                ->minLength(2)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('middle_name')
                                ->minLength(2)
                                ->maxLength(255),
                            Select::make("gender")
                                ->options([
                                    "Male" => __("Male"),
                                    "Female" => __("Female")
                                ])->default(1),
                            Forms\Components\DatePicker::make('date_of_birth'),
                            Forms\Components\TextInput::make('identityNumber_id')
                                    ->numeric(),
                        ]),
                ]),
                
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->maxLength(255),
                                Toggle::make("can_connect")
                                    ->dehydrated(false)
                            ])->columns(1)
                            ->columnSpan(1),
                                Repeater::make("addressCustomers")
                                    ->relationship()
                                    ->label("Address")
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
                                ])->columns(2)
                                ->columnSpan(1)
                                ->mutateRelationshipDataBeforeCreateUsing(function(array $data){
                                    return self::processFillTable($data, Address::class, "address_id");
                                })
                    ])->columns(2)
                
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fullname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('identityNumber_id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('fullcustomeraddress')
                ->label("Full address")
                ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
