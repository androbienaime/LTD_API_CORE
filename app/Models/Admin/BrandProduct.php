<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
