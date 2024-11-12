<?php

namespace App\Core\Trait\Concerns;

use App\Core\States\GeneralStatus\ActiveModelTransition;
use App\Core\States\GeneralStatus\ActiveState;
use App\Core\States\GeneralStatus\BlockedState;
use App\Core\States\GeneralStatus\BlokedModelTransition;
use App\Core\States\GeneralStatus\InactiveModelTransition;
use App\Core\States\GeneralStatus\InactiveState;
use App\Core\States\GeneralStatus\SuspendedModelTransition;
use App\Core\States\GeneralStatus\SuspendedState;
use Filament\Facades\Filament;
use Spatie\ModelStates\HasStates;

trait HasGeneralStatus
{
    use HasStates;
//        HasGuardSpecificStatus;
    public function activated(){
        $this->status->transition(new ActiveModelTransition($this));
        return $this;
    }

    public function inactivated(){
        $this->status->transition(new InactiveModelTransition($this));
        return $this;
    }

    public function blocked(?string $reason = null){
        $this->status->transition(new BlokedModelTransition($this, $reason));
        return $this;
    }

    public function suspended(?string $reason = null){
        $this->status->transition(new SuspendedModelTransition($this, $reason));
    }

    public function updateStatusData(array $data): void
    {
        $this->status_data = array_merge($this->status_data ?? [], $data);
        $this->save();
    }

    public function isActivated(){
        if($this->status instanceof(ActiveState::class)){
            return true;
        }
        return false;
    }

    public function getAvailableStatus(): array
    {
        $currentState = $this->status;

        // Récupère les états autorisés pour le guard actuel
        $allowedStates = static::getAllowedStatesForCurrentGuard();

        // Filtre les états autorisés selon les transitions possibles depuis l'état actuel
        return collect($allowedStates)
            ->filter(function ($stateClass) use ($currentState) {
                // Règle supplémentaire : empêcher la transition de 'bloqué' à 'actif' pour le guard 'account'
                if (Filament::getAuthGuard() === 'account' && $currentState instanceof BlockedState && $stateClass === ActiveState::class) {
                    return false;
                }
                // Vérifie les transitions possibles pour les autres cas
                return $currentState->canTransitionTo($stateClass);
            })
            ->values()
            ->toArray();
    }


        public function changeStatus(string $newState, ?string $reason = null): void
    {
        if ($this->status->canTransitionTo($newState)) {
            switch ($newState) {
                case ActiveState::class:
                    $this->activated();
                    break;
                case InactiveState::class:
                    $this->inactivated();
                    break;
                case BlockedState::class:
                    $this->blocked($reason);
                    break;
                case SuspendedState::class:
                    $this->suspended($reason);
                    break;
                default:
                    throw new \Exception("État non supporté");
            }
        } else {
            throw new \Exception("Transition non autorisée");
        }
    }

    protected static function getAllowedStatesForCurrentGuard(): array
    {
        $guard = Filament::getAuthGuard();

        return match($guard) {
            'account' => [ActiveState::class, InactiveState::class],
            'web' => [ActiveState::class, InactiveState::class, BlockedState::class, SuspendedState::class],
            'api' => [ActiveState::class, InactiveState::class, BlockedState::class],
            default => []
        };
    }


}
