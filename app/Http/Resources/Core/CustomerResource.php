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
            "id" => $this->id,
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "name" => $this->name,
            "middle_name" => $this->middle_name,
            "gender" => $this->gender,
            "phone" => $this->phone,
            "phone_code" => $this->phone_code,
            "identityNumber_id" => $this->identityNumber_id,
            "email" => $this->email,
            "date_of_birth" => $this->date_of_birth,
            "shop_id" => new ShopResource($this->shop),
            "merchant_id" => new AccountResource($this->account)
        ];
    }
}
