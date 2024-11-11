<?php

namespace App\Core\States\GeneralStatus;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\Transition;

class InactiveModelTransition extends Transition
{
    protected Model $model;
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle() : Model{
        $this->model->status = new InactiveState($this->model);
        $this->model->save();

        return $this->model;
    }
}
