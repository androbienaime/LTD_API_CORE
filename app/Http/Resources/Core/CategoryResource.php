<?php

namespace App\Http\Resources\Core;

use App\Models\Core\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            "name" => $this->name,
            "parent" => $this->parent_id,
            "coverImage" => $this->getFirstMedia() ? $this->getFirstMedia()->getFullUrl("thumb") : null,
            'images' => $this->getMedia()->map(function($media){
                // return $media->id."/".$media->file_name;
                return $media->getFullUrl();
            }),
        ];
    }
}
