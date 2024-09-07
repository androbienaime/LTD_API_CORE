<?php

namespace App\Models\Core;

use App\Models\Core\Value;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Core\DeclinationValue;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\DeclinationProduct;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Declination extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        "image",
        "declination",
        "reference",
        "price",
        "quantity",
        "product_id"
    ];

    public function attributeDeclinations() : HasMany{
        return $this->hasMany(AttributeDeclination::class);
    }

    public function declinationProducts() : HasMany{
        return $this->hasMany(DeclinationProduct::class);
    }

    public function values(): BelongsToMany{
        return $this->belongsToMany(Value::class, "declination_values");
    }
}
