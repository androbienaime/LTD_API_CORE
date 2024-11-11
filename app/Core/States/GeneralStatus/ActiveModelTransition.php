<?php

namespace App\Core\States\GeneralStatus;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\Transition;

class ActiveModelTransition extends Transition
{
    protected Model $model;
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle() : Model{
        $this->model->status = new ActiveState($this->model);
        $this->model->save();

        return $this->model;
    }
}
