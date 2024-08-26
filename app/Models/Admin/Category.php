<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "category_parent"
    ];

    public function categoryProducts() : HasMany{
        return $this->hasMany(CategoryProduct::class);
    }
}
