<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "middle_name" => $this->middle_name,
            "gender" => $this->gender,
            "identityNumber_id" => $this->identityNumber_id,
            "email" => $this->email,
            "date_of_birth" => $this->date_of_birth,
            "shop_id" => new ShopResource($this->shop),
            "merchant_id" => new AccountResource($this->account)
        ];
    }
}
