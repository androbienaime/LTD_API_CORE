<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;

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
