<?php

namespace App\Http\Resources\Core;

use App\Models\Core\Declination;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "sub_totals" => $this->sub_totals,
            "quantity" => $this->quantity,
            "discount" => $this->discount,
            "delivery" => new DeliveryResource($this->delivery),
            "product" => new ProductResource($this->product),
            "order_product_declination" => new DeclinationResource($this->Declinations),
            
        ];
    }
}
