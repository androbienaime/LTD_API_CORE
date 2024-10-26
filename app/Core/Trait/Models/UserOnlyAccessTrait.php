<?php

namespace App\Core\Trait\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait UserOnlyAccessTrait
{
    protected static function OnlyAccessUser() : void
    {
        if(!auth("web")->check()){
            abort(403, __("Access UnAuthorized"));
        }
    }
}
