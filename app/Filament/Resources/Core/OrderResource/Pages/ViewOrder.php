<?php

namespace App\Filament\Resources\Core\OrderResource\Pages;

use Filament\Actions;
use App\Models\Core\Order;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Core\OrderResource;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                    ->tooltip(trans('filament-ecommerce::messages.orders.actions.print'))
                    ->icon('heroicon-s-printer')
                    ->openUrlInNewTab(true)
                    ->url(fn($record) => route('invoice.show', Order::find($record->id)))
                    ->iconButton(),
        ];
    }
}
