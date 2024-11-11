<?php

namespace App\Core\ResourceModules;

use App\Core\States\GeneralStatus\ActiveModelTransition;
use App\Core\States\GeneralStatus\ActiveState;
use App\Core\States\GeneralStatus\BlockedState;
use App\Core\States\GeneralStatus\InactiveState;
use App\Core\States\GeneralStatus\SuspendedState;
use App\Core\States\Order\CancelledState;
use App\Core\States\Order\DeliveredState;
use App\Core\States\Order\PendingState;
use App\Core\States\Order\ProcessingState;
use App\Core\States\Order\ReturnedState;
use App\Core\States\Order\ShippedState;
use App\Models\Core\Order;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

trait HasResourceStatus
{
    public static function ActionStatus()
    {
        return Action::make('changerStatut')
            ->label('Changer')
            ->color('primary')
            ->tooltip("Changer le status")
            ->icon('heroicon-o-arrow-path')
            ->form([
                Select::make('status')
                    ->label('Nouveau statut')
                    ->options(function($record){
                        return collect($record->getAvailableStatus())
                            ->map(fn (string $state) => [
                                'value' => $state,
                                'label' => (new $state($record))->label()
                            ])
                            ->pluck('label', 'value')
                            ->toArray();
                    })                    ->live()
                    ->required(),
                Textarea::make("reason")
                    ->hidden(function (Get $get) {
                        $path = "App\Core\States\GeneralStatus\\";
                        $hasReason = [$path . "BlockedState", $path . "SuspendedState"];
                        return !in_array($get("status"), $hasReason);
                    }),
            ])
            ->action(function ($record, $data) {
                try {
                    // Déplacement de la logique de changement d'état dans le modèle
                    $record->changeStatus($data['status'], $data['reason'] ?? null);

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
            ->modalButton('Enregistrer');
    }

    public static function TablesStatus()
    {
        return TextColumn::make('status')
            ->formatStateUsing(fn ($record) => $record->status->label())
            ->badge()
            ->color(fn ($record): string => $record->status->color());
    }
}
