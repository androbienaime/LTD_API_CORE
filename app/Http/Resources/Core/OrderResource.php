<?php

namespace App\Http\Resources\Core;

use App\Models\Core\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "order_amount" => $this->order_amount,
            "total_amount_order" => $this->total_amount_order,
            "order_products" => OrderProductResource::collection($this->orderProducts) ,
            "customer" => new CustomerResource($this->customer),
            "reference_order" => $this->reference_order,
            "secure_key" => $this->secure_key,
            "currency" => new CurrencyResource($this->currency),
            "state" => $this->state->label(),
            "state_data" => $this->state_data,
            "account" => new AccountResource($this->whenHas("account")),
            "merchant" => new AccountResource($this->whenHas("account")),
            "coupon" => $this->coupon,
            "delivery" => new DeliveryResource($this->delivery),
            "has_delivery" => $this->has_delivery,
            "balance" => $this->balance,
            "total_discount" => $this->total_discount,
        ];
    }
}
