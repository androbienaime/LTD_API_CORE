<?php

namespace App\Core\Trait\Concerns;

use App\Core\States\GeneralStatus\ActiveState;
use App\Core\States\GeneralStatus\BlockedState;
use App\Core\States\GeneralStatus\InactiveState;
use App\Core\States\GeneralStatus\SuspendedState;
use Illuminate\Support\Facades\Auth;

trait HasStateAuthorization
{
    public function canTransitionTo(string $newState): bool
    {
        $user = Auth("web")->user();

        // Vérifie si l'utilisateur a la permission générale de faire des transitions
        if ($user->cannot('manageStates', $this->model)) {
            return false;
        }

        // Vérifie la permission spécifique pour cette transition
        $transitionName = $this->getTransitionName(static::class, $newState);
        if ($user->cannot($transitionName, $this->model)) {
            return false;
        }

        return true;
    }

    protected function getTransitionName(string $from, string $to): string
    {
        $fromName = class_basename($from);
        $toName = class_basename($to);
        return "transition{$fromName}To{$toName}";
    }


    protected static function getAllowedStatesByGuard(): array
    {
        return [
            'web' => [
                ActiveState::class,
                InactiveState::class,
                BlockedState::class,
                SuspendedState::class
            ],
            'account' => [
                ActiveState::class,
                InactiveState::class
            ],
            'api' => [
                ActiveState::class,
                InactiveState::class,
                BlockedState::class
            ]
        ];
    }

    public function isAllowedForCurrentGuard(): bool
    {
        $currentGuard = Auth::getDefaultDriver();
        $allowedStates = static::getAllowedStatesByGuard()[$currentGuard] ?? [];

        return empty($allowedStates) || in_array(static::class, $allowedStates);
    }

    public function validateGuardAccess(): void
    {
        if (!$this->isAllowedForCurrentGuard()) {
            throw new UnauthorizedStateException(
                "L'état " . class_basename(static::class) .
                " n'est pas autorisé pour le guard " . Auth::getDefaultDriver()
            );
        }
    }
}
