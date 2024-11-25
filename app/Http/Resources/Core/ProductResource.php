<?php

namespace App\Http\Resources\Core;

use App\Models\Core\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   
        
        return [
            "name" => $this->name,
            "slug" => $this->slug,
            "article" => $this->article,
            "description" => $this->description,
            "price" => $this->price,
            "coverImage" => $this->getFirstMedia() ? $this->getFirstMedia()->getUrl("thumb") : null,
            'images' => $this->getMedia()->map(function($media){
                // return $media->id."/".$media->file_name;
                return $media->getUrl();
            }),
            "declination" => Product::hasDeclinations($this->resource) ? DeclinationResource::collection($this->declinations) : null,
            "shop" => new ShopResource($this->shop),
            "currency" => $this->currency,
            "categories" => CategoryResource::collection(($this->whenLoaded('categories'))),
            "brands" => BrandResource::collection(($this->whenLoaded('brands'))),
            "discount" => new ProductDiscountResource($this->productDiscount)
        ];

        
    }
}
