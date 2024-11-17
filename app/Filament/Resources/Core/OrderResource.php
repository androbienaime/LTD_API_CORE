<?php

namespace App\Filament\Resources\Core;

use App\Core\ResourceModules\Concerns\HasOrderTotal;
use App\Core\ResourceModules\Order\OrderCouponResource;
use App\Core\ResourceModules\Order\OrderDeliveryResource;
use App\Core\ResourceModules\Order\OrderProductResource;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Session;
use App\Models\Core\Currency;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Core\Order;
use Filament\Tables\Table;
use App\Core\Trait\ProductTrait;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Core\Trait\GenerateFieldsTrait;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\Core\OrderResource\Pages;

/**
 * OrderResource Class
 * Manages order-related operations in the Filament admin panel
 */
class OrderResource extends Resource
{
    use GenerateFieldsTrait, ProductTrait, HasOrderTotal;

    protected static ?string $model = Order::class;
    protected static $values = [];
    protected static int $currency = 1;

    public static function getNavigationGroup() : string {
        return __("Orders");
    }

    public static function getNavigationLabel() : string{
        return __("Orders");
    }


    /**
     * @return void
     */
    protected static function configureResource(): void
    {
        // Vérifiez si une devise est sélectionnée
        if (session()->has('currency')) {
            // Faites quelque chose avec la devise si nécessaire
            self::$currency = session('currency');
        }

        // Réinitialiser l'indicateur de modal pour éviter sa réouverture
        session()->forget('show_modal_currency');
    }

    /**
     * Defines the form structure for creating/editing orders
     */
    public static function form(Form $form): Form
    {
        self::configureResource();
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Select::make('customer_id')
                                ->relationship(
                                    name:'customer',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('firstname')->orderBy('lastname'),
                                    )
                                ->label(__("Customer"))
                                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->firstname} {$record->lastname}")
                                ->required()
                                ->preload()
                                ->searchable()
                                ->createOptionForm([
                                    Grid::make()
                                            ->schema([
                                                TextInput::make('firstname')
                                                    ->label(__("First name"))
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('lastname')
                                                    ->label(__('Last name'))
                                                    ->maxLength(255),
                                            ])->columns(2),
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('middle_name')
                                                ->label(__("Middle Name"))
                                                ->maxLength(255),
                                            Select::make('gender')
                                                ->label(__('Gender'))
                                                ->options([
                                                    "Male" => __("Male"),
                                                    "Female" => __("Female"),
                                                ])
                                                ->required(),
                                        ])->columns(2),
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('identity_number')
                                                ->label(__("No ID"))
                                                ->numeric()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->email()
                                                ->maxLength(255),
                                        ])->columns(2),
                                    Grid::make()
                                        ->schema([
                                            DateTimePicker::make('date_of_birth')
                                                ->label('Date of Birth')
                                                ->minDate(now()->subYear(90))
                                                ->maxDate(now()->subYear(10))
                                        ])->columns(2),

                                ]),

                        Select::make('currency_id')
                            ->label(__("Currency"))
                            ->relationship("currency", "iso_code", modifyQueryUsing: fn(Builder $query) => $query->where("is_active", true))
                            ->afterStateUpdated(function(callable $set, callable $get) {
                                $set('prefix_field', Currency::where("id", $get("currency_id"))->first()->symbol);
                                self::updateTotals($get, $set);
                            })
                            ->disabled(true)
                            ->dehydrated(true)
                            ->default(self::$currency)
                            ->reactive()
                            ->required(),
                    ]),
                Section::make()
                    ->schema(
                        OrderProductResource::form()
                    )->columns(1),

                Section::make()
                    ->schema([
                        OrderDeliveryResource::form()
                    ]),
                Section::make()
                  ->schema([
                    Forms\Components\Grid::make()
                    ->schema(OrderCouponResource::form())
                    ->columnSpanFull(),
                    TextInput::make('total_discount')
                        ->label(__("Discount"))
                        ->reactive()
                        ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("currency_id"))->first()->symbol)
                        ->disabled()
                        ->dehydrated(true)
                        ->default(0),
                      TextInput::make('total_amount_order')
                          ->label(__("Totals"))
                          ->readOnly()
                          ->reactive()
                          ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("currency_id"))->first()->symbol)
                          ->default(0)
                          ->required(),
                      Forms\Components\TextInput::make('order_amount')
                          ->label(__("Payment"))
                          ->required()
                          ->default(0)
                          ->minValue(0)
                          ->lte("total_amount_order")
                          ->live(onBlur: true)
                          ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("currency_id"))->first()->symbol)
                          ->afterStateUpdated(function (Get $get, Set $set) {
                              self::updateBalance($get, $set);
                          })
                          ->numeric(),
                      Forms\Components\TextInput::make('balance')
                          ->label(__("Balance"))
                          ->disabled()
                          ->dehydrated(true)
                          ->reactive()
                          ->prefix(fn(callable $get) => $get("prefix_field") ?: Currency::where("id", $get("currency_id"))->first()->symbol)
                          ->numeric(),
                  ])->columns(3),
        ]);
    }
    /**
     * Defines the table structure for listing orders
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.fullname')
                    ->label(__("Full name"))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(function($record){
                        if(self::hasCustomerAddress($record->customer)){
                            $addressCustomer = $record->customer->addressCustomers->first()->address;
                            return $addressCustomer->phone_code . " " . $addressCustomer->phone;
                        }
                    })
                    ->sortable(['customer.firstname', 'customer.lastname']),
                Tables\Columns\TextColumn::make('delivery.address.country.name')
                    ->label(__("Shipping Address"))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->description(function($record){
                        if($record->delivery){
                            $delivery = $record->delivery;
                            if ($region = $delivery->address) {
                                $state = $region->state->name ?? "";
                                $city = $region->city->name ?? "";
                                return "{$state}, {$city}, {$region->postal_code}";
                            }
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(['customer.firstname', 'customer.lastname']),
                Tables\Columns\TextColumn::make('total_amount_order')
                    ->money(fn(Order $record) => $record->currency->iso_code)
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_amount')
                    ->label(__("Payment"))
                    ->money(fn(Order $record) => $record->currency->iso_code)
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->formatStateUsing(fn (Order $record) => $record->state->label())
                    ->badge()
                    ->color(fn (Order $record): string => $record->state->color()),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('reference_order')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('merchant.firstname')
                    ->numeric()
                    ->label(__("Merchant"))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
                Tables\Columns\TextColumn::make('secure_key')
                    ->limit(32)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ,
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
                Tables\Actions\Action::make('print')
                    ->tooltip(trans('filament-ecommerce::messages.orders.actions.print'))
                    ->icon('heroicon-s-printer')
                    ->openUrlInNewTab(true)
                    ->url(fn($record) => route('invoice.show', Order::find($record->id)))
                    ->iconButton(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('changerStatut')
                    ->label('Changer le statut')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path')
                    ->iconButton()
                    ->form([
                        Select::make('state')
                            ->label('Nouveau statut')
                            ->options(function(Order $record){
                                return collect($record->getAvailableStates())
                                        ->map(fn (string $state) => [
                                            'value' => $state,
                                            'label' => (new $state($record))->label()
                                        ])
                                        ->pluck('label', 'value')
                                        ->toArray();
                            })
                            ->default(fn (Order $record): array => [
                                'state' => get_class($record->state)
                            ])
                            ->live()
                            ->required()
                            ->disabled(fn (Order $record): bool => !auth(Filament::getAuthGuard())->user()->can('update', $record)),
                            Forms\Components\Textarea::make("reason")
                            ->hidden(function (Get $get){
                                $path = "App\Core\States\Order";
                                $hasReason = [$path."\CancelledState", $path."\ReturnedState"];
                                    if (in_array($get("state"), $hasReason)) {
                                        return false;
                                    }
                                return true;
                            }),
                            TextInput::make("tracking_number")
                                    ->hidden(fn(Get $get) => $get("state") != "App\Core\States\Order\ShippedState")
                    ])
                    ->action(function (Order $record, $data) {
                        try {
                            // Déplacement de la logique de changement d'état dans le modèle
                            $record->changeStatus($data['state'], $data['reason'] ?? null, trackingNumber : $data["tracking_number"] ?? null);

                            Notification::make()
                                ->success()
                                ->title('État mis à jour')
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->send();
                        }
                      })
                    ->requiresConfirmation()
                    ->modalHeading('Changer le statut')
                    ->modalSubheading('Sélectionnez le nouveau statut pour cet enregistrement.')
                    ->modalButton('Enregistrer')
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
//            RelationManagers\ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    // public static function updateDeclinationsPrice(Get $get, Set $set, $livewire){

    //     $orderProducts = $get('orderProducts');
    //     $declinationPrices = collect($orderProducts)->map(function ($item) {
    //         $declination = Declination::find($item['declination_id'] ?? null);
    //         return $declination ? $declination->price : 0;
    //     })->toArray();

    //     $livewire->declinationPrices = $declinationPrices;
    // }

}
