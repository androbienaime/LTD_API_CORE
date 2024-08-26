<?php

namespace App\Http\Controllers\Api\Core;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Core\ProductResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::with("media")->get()->map(function($product){
            return [
                    "id"=> $product->id,
                    "name" => $product->name,
                    "description" => $product->description,
                    "price" => $product->price,
                    "coverImage" => $product->getFirstMedia() ? $product->getFirstMedia()->getUrl("thumb") : null,
                    'images' => $product->getMedia()->map(function($media){
                        // return $media->id."/".$media->file_name;
                        return $this->getRelativePath($media->getUrl());
                    })
                ];
        }));
    }

    public function getRelativePath($url){
        return $url;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
