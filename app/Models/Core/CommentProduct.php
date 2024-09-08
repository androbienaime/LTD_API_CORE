<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommentProduct extends Model
{
    use HasFactory;

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function replies(){
        return $this->hasMany(CommentProduct::class, 'parent_id');
    }

    
}
