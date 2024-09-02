<?php

namespace App\Models\Admin;

use App\Models\Admin\Value;
use App\Models\Admin\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Pivot
{
    protected $fillable = [
        "attribute_id",
        "value_id"
    ];
    public function attribute() : BelongsTo{
        return $this->belongsTo(Attribute::class);
    }

    public function value() : BelongsTo{
        return $this->belongsTo(Value::class);
    }
}
