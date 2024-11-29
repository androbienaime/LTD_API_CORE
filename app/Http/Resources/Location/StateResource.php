<?php

namespace App\Http\Resources\Location;

use App\Models\Location\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
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
            "state" => $this->name,
            "country" => new CountryResource($this->whenLoaded('country'))
        ];
    }
}
