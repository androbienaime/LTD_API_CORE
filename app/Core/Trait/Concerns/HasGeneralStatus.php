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
use Spatie\ModelStates\HasStates;

trait HasGeneralStatus
{
    use HasStates;
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

    /**
     * @return array
     */
    public function getAvailableStatus(): array
    {
        $currentState = $this->status;
        return self::getStates()["status"]
            ->filter(fn ($stateClass) =>  $currentState->canTransitionTo($stateClass))
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
}
