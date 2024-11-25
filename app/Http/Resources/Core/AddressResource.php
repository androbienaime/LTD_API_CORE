<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'country' => $this->country,
            "state" => $this->state,
            'city' => $this->city,
            "city_2" => $this->city2,
            "phone" => $this->phone,
            "phone_mobile" => $this->phone_mobile,
            "email" => $this->email,
            "phonecode" => $this->phonecode,
            "address1" => $this->address1,
            "address2" => $this->address2,
            "alias" => $this->alias,
            "company" => $this->company,
            "active" => $this->active
        ];
    }
}
