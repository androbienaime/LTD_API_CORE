<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Declination extends Model
{
    use HasFactory;

    protected $fillable = [
        "image",
        "declination",
        "reference",
        "price",
        "quantity",
    ];

    public function attributeDeclinations() : HasMany{
        return $this->hasMany(AttributeDeclination::class);
    }

    public function declinationProducts() : HasMany{
        return $this->hasMany(DeclinationProduct::class);
    }
}
