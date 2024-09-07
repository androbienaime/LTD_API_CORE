<?php

namespace App\Models\Core;

use App\Models\Core\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Value extends Model
{
    use HasFactory;

    protected $fillable = [
        "value",
        "color",
        "url",
        "meta_title",
        "indexable"
    ];
    public function attributeValue() : HasMany{
        return $this->hasMany(AttributeValue::class);
    }
}
