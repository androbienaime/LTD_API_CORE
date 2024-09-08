<?php

namespace App\Models\Core;

use App\Models\Core\CategoryProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "category_parent",
        "slug",
        "parent_id"
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function Product() : BelongsToMany{
        return $this->belongsToMany(CategoryProduct::class);
    }
    
}
