<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
