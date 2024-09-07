<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Core\BrandProduct;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        "name",
        "logo",
        "color"
    ];

    public function brandProducts() : BelongsTo{
        return $this->belongsTo(BrandProduct::class);
    }
    public function products() : BelongsToMany{
        return $this->belongsToMany(Product::class);
    }
}
