<?php

namespace App\Core\ResourceModules\Concerns;

use App\Filament\Resources\Core\OrderResource;
use App\Models\Core\Currency;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

trait HasSelectCurrency
{

    /**
     * @return Action
     */
    public function selectCurrency(): Action
    {
        return Action::make('selectCurrency')
            ->form([
                Select::make('currency')
                    ->label('Choisissez votre devise')
                    ->options(function () {
                        return Currency::query()
                            ->where('is_active', true)
                            ->pluck('iso_code', 'id');
                    })
                    ->default(function () {
                        return Currency::query()
                            ->where('is_active', true)
                            ->first()?->id;
                    })
                    ->required()
                    ->reactive(),
            ])
            ->action(function (array $data){
                // Enregistrer la devise dans la session
                session(['currency' => $data['currency']]);

                // Nettoyer l'indicateur de modal
                session()->forget('show_modal_currency');


                Notification::make()
                    ->success()
                    ->title('Devise selectionne avec success')
                    ->send();
                return redirect(OrderResource::getUrl('create'));
            })
            ->modalWidth('md') // Taille du modal
            ->modalAlignment('center') // Aligner au centre
            ->modalCloseButton(false) // Désactiver le bouton de fermeture
            ->closeModalByClickingAway(false) // Désactiver fermeture en cliquant à l’extérieur
            ->closeModalByEscaping(false) // Désactiver la fermeture avec "Escape"
            ->modalCancelAction(false)
            ->visible(function() {
                return session('show_modal_currency', true);
            });

    }
}
