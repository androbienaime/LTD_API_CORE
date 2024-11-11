<?php

namespace App\Core\States\GeneralStatus;

use App\Core\States\GeneralStatus\GeneralStatusState;

class ActiveState extends GeneralStatusState
{

    public function color(): string
    {
        return "success";
    }

    public function label(): string
    {
        return "active";
    }
}
