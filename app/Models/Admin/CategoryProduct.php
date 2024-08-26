<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
