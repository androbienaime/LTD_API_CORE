<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "logo",
        "color"
    ];

    public function brandProducts() : BelongsTo{
        return $this->belongsTo(BrandProduct::class);
    }
}
