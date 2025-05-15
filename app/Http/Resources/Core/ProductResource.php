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
            "coverImage" => $this->getFirstMedia() ? $this->getFirstMedia()->getFullUrl("thumb") : null,
            'images' => $this->getMedia()->map(function($media){
                return $media->getFullUrl();
            }),
            "declination" => Product::hasDeclinations($this->resource) ? DeclinationResource::collection($this->declinations) : null,
            "shop" => new ShopResource($this->shop),
            "currency" => $this->currency,
            "categories" => CategoryResource::collection(($this->whenLoaded('categories'))),
            "brands" => BrandResource::collection(($this->whenLoaded('brands'))),
            "discount" => new ProductDiscountResource($this->productDiscount),
            "seo" => $this->ltspSeo,
            "has_unlimited_stock" => $this->has_unlimited_stock,
            "stock_quantity" => $this->stock_quantity ?? null

        ];

        
    }
}
