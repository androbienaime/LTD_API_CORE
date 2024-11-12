<?php

namespace App\Filament\Resources\Core\CurrencyResource\Pages;

use App\Filament\Resources\Core\CurrencyResource;
use App\Services\CurrencyServices;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCurrencies extends ListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('Update Currencies')
                ->label('Import Currencies')
                ->action(function () {
                    $importService = app(CurrencyServices::class);
                    $result = $importService->updateRates();

                    Notification::make()
                        ->title("updated {$result['updated']} existing ones")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
        ];
    }
}
