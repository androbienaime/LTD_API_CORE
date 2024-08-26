<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AttributeDeclination extends Pivot
{
    use HasFactory;

    public function attribute() : BelongsTo{
        return $this->belongsTo(Attribute::class);
    }

    public function declination() : BelongsTo{
        return $this->belongsTo(Declination::class);
    }
}
