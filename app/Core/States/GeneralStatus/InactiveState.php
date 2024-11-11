<?php

namespace App\Core\States\GeneralStatus;

use App\Core\States\GeneralStatus\GeneralStatusState;

class InactiveState extends GeneralStatusState
{

    public function color(): string
    {
        return "gray";
    }

    public function label(): string
    {
        return "Inactive";
    }
}
