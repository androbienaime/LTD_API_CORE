<?php

namespace App\Models\Core;

use App\Models\Core\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'type', 'amount', 'is_limited', 'end_at', 'use_limit', 'use_limit_by_user', 'order_total_limit', 'is_activated', 'is_marketing', 'marketer_name', 'marketer_type', 'marketer_amount', 'marketer_amount_max', 'marketer_show_amount_max', 'marketer_hide_total_sales', 'is_used', 'apply_to', 'except', 'created_at', 'updated_at'];

    protected $casts = [
        "apply_to" => "json",
        "except" => "json",
        "is_activated" => "boolean",
        "is_marketing" => "boolean",
        "marketer_show_amount_max" => "boolean",
        "marketer_hide_total_sales" => "boolean",
        "is_limited" => "boolean",
    ];

    public function discount(?float $total=null)
    {
        if($this->type === 'percentage_coupon'){
            return $total * $this->amount / 100;
        }
        else {
            return $this->amount;
        }
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        if(class_exists(Order::class)){
            return $this->hasMany(Order::class);
        }
    }
}
