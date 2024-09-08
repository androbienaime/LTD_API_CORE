<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeliveryProduct extends Pivot
{
    use HasFactory;
   
    public function delivery() : BelongsTo{
        return $this->belongsTo(Delivery::class);
    }

    public function product() : BelongsTo{
        return $this->belongsTo(Product::class);
    }
    public function categories() : BelongsToMany{
        return $this->belongsToMany(Category::class);
    }
}
