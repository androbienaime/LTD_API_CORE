<?php

namespace App\Models\Core;

use App\Models\Core\Brand;
use App\Models\Core\Product;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandProduct extends Pivot
{
    use HasFactory;

    public function brand() : BelongsTo{
        return $this->belongsTo(Brand::class);
    }

    public function product() : BelongsTo{
        return $this->belongsTo(Product::class);
    }
}
