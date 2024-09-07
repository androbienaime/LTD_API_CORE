<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrier extends Model
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        "carrier_name",
        "transit_time",
        "speed_grade",
        "logo",
        "tracking_url",
        "free_shipping",
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
