<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use App\Models\Core\Attribute;
use App\Models\Core\AttributeDeclination;
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
