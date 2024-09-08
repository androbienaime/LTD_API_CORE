<?php

namespace App\Models\Core;

use App\Models\Core\Value;
use App\Models\Core\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "type"
    ];

    public function attributeDeclinations() : HasMany{
        return $this->hasMany(AttributeDeclination::class);
    }

    public function attributeValue() : HasMany{
        return $this->hasMany(AttributeValue::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(Value::class);
    }
}
