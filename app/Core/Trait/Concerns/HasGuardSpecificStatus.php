<?php

namespace App\Core\Trait\Concerns;

use App\Core\States\GeneralStatus\ActiveState;
use App\Core\States\GeneralStatus\BlockedState;
use App\Core\States\GeneralStatus\InactiveState;
use App\Core\States\GeneralStatus\SuspendedState;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasGuardSpecificStatus
{
    public static function bootHasGuardSpecificStatus()
    {
        // Cette méthode est automatiquement appelée lors du boot du modèle
        static::addGlobalScope('guard_states', function (Builder $builder) {
            $allowedStates = static::getAllowedStatesForCurrentGuard();

            if (!empty($allowedStates)) {
                $stateColumn = (new static)->getStateColumn();
                $builder->whereIn($stateColumn, $allowedStates);
            }
        });
    }

    protected static function getAllowedStatesForCurrentGuard(): array
    {
        $guard = Filament::getAuthGuard();

        return match($guard) {
            'account' => [
                ActiveState::class,
                InactiveState::class,
            ],
            'web' => [
                ActiveState::class,
                InactiveState::class,
                BlockedState::class,
                SuspendedState::class,
            ],
            'api' => [
                ActiveState::class,
                InactiveState::class,
                BlockedState::class,
            ],
            default => []
        };
    }

    protected function getStateColumn(): string
    {
        // Par défaut, on utilise 'status' comme colonne d'état
        return defined(static::class . '::STATE_COLUMN') ?
            static::STATE_COLUMN :
            'status';
    }

    public function initializeHasGuardSpecificStates()
    {
        // Cette méthode est appelée lors de l'initialisation du modèle
        $this->append('available_transitions');

        // Intercepte les transitions d'état
        static::saving(function ($model) {
            $stateColumn = $model->getStateColumn();
            $newState = $model->getAttribute($stateColumn);

            if ($newState && !in_array($newState, static::getAllowedStatesForCurrentGuard())) {
                throw new \InvalidArgumentException(
                    "L'état {$newState} n'est pas autorisé pour le guard " . Auth::getDefaultDriver()
                );
            }
        });
    }

    public function getAvailableTransitionsAttribute(): array
    {
        $currentState = $this->getAttribute($this->getStateColumn());
        $allowedStates = static::getAllowedStatesForCurrentGuard();

        // Filtrer les transitions possibles selon le guard actuel
        return array_values(array_filter($allowedStates, function($state) use ($currentState) {
            return $state !== $currentState;
        }));
    }

    public function scopeWithoutGuardRestriction(Builder $query): Builder
    {
        return $query->withoutGlobalScope('guard_states');
    }
}
