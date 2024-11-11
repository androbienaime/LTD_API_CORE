<?php

namespace App\Core\States\GeneralStatus;

use App\Core\States\GeneralStatus\GeneralStatusState;

class BlockedState extends GeneralStatusState
{

    public function color(): string
    {
        return "danger";
    }

    public function label(): string
    {
        return "Blocked";
    }
}
