<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "width" => $this->width,
            "heigth" => $this->heigth,
            "depth" => $this->depth,
            "weigth" => $this->weigth,
            "costs" => $this->costs,
            'delivery_mode' => $this->delivery_mode,
            "delivery_date" => $this->delivery_date,
            "carrier_id" => $this->carrier,
            "address" => new AddressResource($this->address)
        ];
    }
}
