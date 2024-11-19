<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            "discount" => $this->discount,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "description" => $this->description,
        ];
    }
}
