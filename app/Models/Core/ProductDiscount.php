<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        "has_discount",
        "discount",
        "start_date",
        "end_date",
        "description"
    ];
}
