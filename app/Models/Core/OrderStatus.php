<?php

namespace App\Models\Core;

use App\Models\Core\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
