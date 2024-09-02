<?php

namespace App\Models\Admin;

use App\Models\Admin\Value;
use App\Models\Admin\Declination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeclinationValue extends Model
{
    use HasFactory;

    protected $fillable = [
        "value_id",
        "declination_id"
    ];

    public function declination() : BelongsTo{
        return $this->belongsTo(Declination::class);
    }

    public function values() : BelongsTo{
        return $this->belongsTo(Value::class);
    }
}
