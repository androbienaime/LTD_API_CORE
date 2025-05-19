<?php

namespace App\Http\Resources\Core;

use App\Filament\Resources\Core\AttributeResource;
use Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeclinationResource extends JsonResource
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
            "price" => $this->price,
            "sku" => $this->reference,
            "quantity" => $this->quantity,
            "declination_images" => $this->getMedia()->map(function($media){
                // return $media->id."/".$media->file_name;
                return $media->getFullUrl();
            }), 
            "values" => ValueResource::collection($this->values)
        ];
    }
}
