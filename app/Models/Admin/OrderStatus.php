<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "action",
        "icon",
        "color",
    ];

    public function order(){
        return $this->hasMany(Order::class);
    }

    public static function AllStatusBadge(){
        $badge = [];
        foreach (self::all() as $status){
            $badge = $badge[$status->name] = $status->color;
        }

        return $badge;
    }
}
