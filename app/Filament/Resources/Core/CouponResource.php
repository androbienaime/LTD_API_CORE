<?php

namespace App\Filament\Resources\Core;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Core\Coupon;
use Illuminate\Support\Str;
use App\Models\Core\Product;
use App\Models\Core\Category;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Core\CouponResource\Pages;
use App\Filament\Resources\Core\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\Core\CouponResource\RelationManagers;
use App\Filament\Resources\Core\CouponResource\Pages\ListCoupons;
use App\Filament\Resources\Core\CouponResource\Pages\CreateCoupon;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup() : string {
        return __("Orders");
    }

    public static function getNavigationLabel() : string{
        return __("Coupons");
    }

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                ->unique(ignoreRecord: true)
                ->label(trans('code'))
                ->default(Str::random(6))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('type')
                ->searchable()
                ->label(trans('type'))
                ->options([
                    'discount_coupon' => trans('discount_coupon'),
                    'percentage_coupon' => trans('coupon'),
                ])
                ->default('discount_coupon'),
            Forms\Components\TextInput::make('amount')
                ->label(trans('amount'))
                ->required()
                ->numeric()
                ->default(0),
            Forms\Components\DatePicker::make('end_at')
                ->label(trans('end_at')),
            Forms\Components\Toggle::make('is_activated')
                ->columnSpanFull()
                ->label(trans('is_activated')),
            Forms\Components\Toggle::make('is_limited')
                ->columnSpanFull()
                ->default(false)
                ->label(trans('is_limited'))
                ->live(),
            Forms\Components\Repeater::make('apply_to')
                ->columnSpanFull()
                ->hidden(fn(Forms\Get $get) => !$get('is_limited'))
                ->label(trans('apply_to'))
                ->schema([
                    Forms\Components\Select::make('model_type')
                        ->label(trans('type'))
                        ->searchable()
                        ->options([
                            Product::class => trans('product'),
                            Category::class => trans('category')
                        ])
                        ->live(),
                    Forms\Components\Select::make('model_id')
                        ->hidden(fn(Forms\Get $get) => $get('model_type') !== Category::class)
                        ->label(trans('category'))
                        ->searchable()
                        ->options(Category::query()->pluck('name', 'id')->toArray()),
                    Forms\Components\Select::make('model_id')
                        ->hidden(fn(Forms\Get $get) => $get('model_type') !== Product::class)
                        ->label(trans('product'))
                        ->searchable()
                        ->options(Product::query()->pluck('name', 'id')->toArray())
                ]),
            Forms\Components\Repeater::make('except')
                ->columnSpanFull()
                ->hidden(fn(Forms\Get $get) => !$get('is_limited'))
                ->label(trans('except'))
                ->schema([
                    Forms\Components\Select::make('model_type')
                        ->label(trans('type'))
                        ->searchable()
                        ->options([
                            Product::class => trans('product'),
                            Category::class => trans('category')
                        ])
                        ->live(),
                    Forms\Components\Select::make('model_id')
                        ->hidden(fn(Forms\Get $get) => $get('model_type') !== Category::class)
                        ->label(trans('category'))
                        ->searchable()
                        ->options(Category::query()->pluck('name', 'id')->toArray()),
                    Forms\Components\Select::make('model_id')
                        ->hidden(fn(Forms\Get $get) => $get('model_type') !== Product::class)
                        ->label(trans('product'))
                        ->searchable()
                        ->options(Product::query()->pluck('name', 'id')->toArray())
                ]),
            Forms\Components\TextInput::make('use_limit')
                ->hidden(fn(Forms\Get $get) => !$get('is_limited'))
                ->label(trans('use_limit'))
                ->numeric()
                ->default(0),
            Forms\Components\TextInput::make('use_limit_by_user')
                ->hidden(fn(Forms\Get $get) => !$get('is_limited'))
                ->label(trans('use_limit_by_user'))
                ->numeric()
                ->default(0),
            Forms\Components\TextInput::make('order_total_limit')
                ->hidden(fn(Forms\Get $get) => !$get('is_limited'))
                ->label(trans('order_total_limit'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_marketing')
                ->columnSpanFull()
                ->live()
                ->label(trans('columns.is_marketing')),
            Forms\Components\TextInput::make('marketer_name')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('marketer_name'))
                ->maxLength(255),
            Forms\Components\TextInput::make('marketer_type')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('marketer_type'))
                ->maxLength(255),
            Forms\Components\TextInput::make('marketer_amount')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('columns.marketer_amount'))
                ->numeric(),
            Forms\Components\TextInput::make('marketer_amount_max')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('columns.marketer_amount_max'))
                ->numeric(),
            Forms\Components\Toggle::make('marketer_show_amount_max')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('marketer_show_amount_max')),
            Forms\Components\Toggle::make('marketer_hide_total_sales')
                ->hidden(fn(Forms\Get $get) => !$get('is_marketing'))
                ->label(trans('marketer_hide_total_sales')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                ->copyable()
                ->icon('heroicon-o-clipboard')
                ->badge()
                ->tooltip(trans('filament-ecommerce::messages.coupons.columns.copy'))
                ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->icon(fn($record) => $record->type === 'discount_coupon' ? 'heroicon-o-receipt-refund' : 'heroicon-o-receipt-percent')
                    ->color(fn($record) => $record->type === 'discount_coupon' ? 'primary' : 'info')
                    ->state(fn($record) => $record->type === 'discount_coupon' ? trans('discount_coupon') : trans('percentage_coupon'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_activated')
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
                Tables\Filters\SelectFilter::make('type')
                ->searchable()
                ->options([
                    'discount_coupon' => trans('discount_coupon'),
                    'percentage_coupon' => trans('percentage_coupon'),
                ]),
                Tables\Filters\TernaryFilter::make('is_activated')
                    ->label(trans('filters.is_activated')),
                Tables\Filters\TernaryFilter::make('is_limited')
                    ->label(trans('filters.is_limited')),
                Tables\Filters\TernaryFilter::make('is_marketing')
                    ->label(trans('filters.is_marketing')),
                Tables\Filters\Filter::make('end_at')
                    ->label(trans('end_at'))
                    ->form([
                        Forms\Components\DatePicker::make('end_at'),
                    ])
                    ->query(function (Builder $query, array $data): Builder
                    {
                        return $query
                            ->when(
                                $data['end_at'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_at', '>=', $date),
                            );
                    })
            ])
            ->actions([
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
