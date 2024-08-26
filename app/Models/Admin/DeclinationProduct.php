<?php

namespace App\Models\Admin;

use App\Models\Admin\Attribute;
use App\Models\Admin\AttributeDeclination;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeclinationProduct extends Pivot
{
    use HasFactory;

    public function declination() : BelongsTo{
        return $this->belongsTo(Declination::class);
    }

    public function product() : BelongsTo{
        return $this->belongsTo(Product::class);
    }

    public function attributeDeclination() : BelongsTo{
        return $this->belongsTo(AttributeDeclination::class);
    }

    public function attribute() : BelongsTo{
        return $this->belongsTo(Attribute::class);
    }
}
