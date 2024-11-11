<?php

namespace App\Core\States\GeneralStatus;

use App\Core\States\GeneralStatus\GeneralStatusState;

class SuspendedState extends GeneralStatusState
{

    public function color(): string
    {
        return "warning";
    }

    public function label(): string
    {
        return "Suspended";
    }
}
