<?php

namespace App\Filament\Resources\Core\OrderResource\Pages;

use App\Core\States\Order\Exception\OrderTransitionException;
use App\Filament\Resources\Core\OrderResource;
use App\Models\Core\Currency;
use App\Models\Core\Order;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Livewire;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.orders.pages.create-order';
    public $defaultAction = "selectCurrency";

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);
        $data['reference_order'] = "FA".Carbon::now()->format("mY")."D".rand(1000, 9999);
        $data['secure_key'] =  Str::random(64);

        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        // Vérifiez si une devise est déjà définie
        if (!session()->has('currency')) {
            // Indiquez que le modal doit être affiché
            session(['show_modal_currency' => true]);
        } else {
            // Une devise est déjà sélectionnée, ne pas afficher le modal
            session(['show_modal_currency' => false]);
        }
    }

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

//    protected function getFormActions(): array
//    {
//        return [
//            $this->testAction(),
//            // Other actions...
//        ];
//    }



}
