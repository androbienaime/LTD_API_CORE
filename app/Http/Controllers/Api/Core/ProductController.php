<?php

namespace App\Http\Controllers\Api\Core;

use Exception;
use Illuminate\Support\Arr;
use App\Models\Core\Product;
use Illuminate\Http\Request;
use App\Models\Core\Currency;
use App\Services\ProductService;
use App\Services\CurrencyServices;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Core\ProductRequest;
use App\Http\Resources\Core\ProductResource;
use App\Core\States\GeneralStatus\ActiveState;
use App\Models\Core\Shop;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Nombre d'éléments par page (par défaut 15)
            
        return ProductResource::collection(
            Product::getAvailableProductsWithMedia()->paginate($perPage)
        );    
    }

    public function productByShop(Request $request, Shop $shop)
    {
        $perPage = $request->get('per_page', 10); // Nombre d'éléments par page (par défaut 15)
            
        return ProductResource::collection(
            Product::getAvailableProductsWithMedia()->paginate($perPage)->where('shop_id', $shop->id)
        );    
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
    public function store(ProductRequest $request)
    {
        $response = ProductService::storeProduct($request);

        return response()->json($response, $response["code"]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        if(Product::findBySlug($slug)){
            return new ProductResource(Product::findBySlug($slug));
        }else{
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
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
    public function update(ProductRequest $request, String $slug)
    {
        if(!Product::findBySlug($slug)){
            return response()->json([
                "success" => false, 
                "message" => "Product not found"
            ]);
        }

        $product = Product::findBySlug($slug);

        $response = ProductService::updateProduct($request, $product);

        return response()->json($response, $response["code"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $slug)
    {
        if(!Product::findBySlug($slug)){
            return response()->json([
                "success" => false, 
                "message" => "Product not found"
            ]);
        }

        $product = Product::findBySlug($slug);

        if($product){
            $product->delete();
            return response()->json([
                "message" => "Destroyed successfull"
            ], 204);
        }else{
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
    }

   
}
