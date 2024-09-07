<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use App\Models\Core\Category;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryProduct extends Pivot
{
    use HasFactory;

    public function category() : BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function product() : BelongsTo{
        return $this->belongsTo(Product::class);
    }
}
