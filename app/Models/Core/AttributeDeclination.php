<?php

namespace App\Models\Core;

use App\Models\Core\Attribute;
use App\Models\Core\Declination;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
