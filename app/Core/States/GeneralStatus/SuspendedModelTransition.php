<?php

namespace App\Core\States\GeneralStatus;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\Transition;

class SuspendedModelTransition extends Transition
{
    protected Model $model;
    protected string $reason;
    protected \DateTime $date_blocked;
    public function __construct(Model $model, ?string $reason = null, ?\DateTime $date_blocked = null){
        $this->model = $model;
        $this->reason = $reason;
        $this->date_blocked = $date_blocked ?? now();
    }

    public function handle() : Model{
        $this->model->status = new SuspendedState($this->model);
        $this->model->save();
        $this->model->updateStatusData(["suspended_reason" => $this->reason, "suspended_at" => $this->date_blocked]);

        return $this->model;
    }
}
