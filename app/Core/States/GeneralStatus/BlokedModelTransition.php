<?php

namespace App\Core\States\GeneralStatus;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\Transition;

class BlokedModelTransition extends Transition
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
        $this->model->status = new BlockedState($this->model);
        $this->model->save();
        $this->model->updateStatusData(["blocked_reason" => $this->reason, "blocked_at" => $this->date_blocked]);

        return $this->model;
    }
}
