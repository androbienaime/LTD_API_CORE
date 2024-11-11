<?php

namespace App\Core\States\GeneralStatus;

use Filament\Forms\Components\Builder\Block;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class GeneralStatusState extends State
{
    public function __construct($model)
    {
        parent::__construct($model);
    }

    abstract public function color () : string;
    abstract public function label () : string;
    public static function config() : StateConfig
    {
        return parent::config()
            ->default(ActiveState::class)
            ->allowTransition(ActiveState::class, InactiveState::class)
            ->allowTransition(ActiveState::class, BlockedState::class)
            ->allowTransition(ActiveState::class, SuspendedState::class)
            ->allowTransition([InactiveState::class, BlockedState::class, SuspendedState::class], ActiveState::class)
            ->registerState([
                ActiveState::class,
                InactiveState::class,
                SuspendedState::class,
                BlockedState::class,
            ]);
    }
}
