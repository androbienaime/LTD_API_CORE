<?php

namespace App\Http\Controllers\Api\Core;

use Exception;
use App\Models\Core\Shop;
use App\Models\Core\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Core\ShopRequest;
use App\Http\Resources\Core\ProductResource;
use App\Http\Resources\Core\ShopResource;
use App\Models\Core\Product;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shops = Shop::with(['account', 'address', 'categories'])->paginate(10);
        return ShopResource::collection($shops);
    }

    public function getProductByShop(Request $request, Shop $shops){
        $perPage = $request->get('per_page', 10); // Nombre d'éléments par page (par défaut 15)

        return ProductResource::collection(
            Product::getAvailableProductsByShopWithMedia($shops)->paginate($perPage)
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
    public function store(ShopRequest $request)
    {
        $validated = $request->validated();
        $account = Account::find($validated['account_id']);

        try{

            DB::beginTransaction();

            if(!$account){
                throw new Exception("Account not found");
            }else{
                if(!Shop::isAccountEligible($account)){
                    throw new Exception("Your account is not eligible for create a shop");
                }
            }
            $shop = Shop::create($validated);
            $shop->account()->sync($validated['account_id']);

            DB::commit();

            return response()->json([
                "success" => true,
                'message' => 'Shop created successfully.',
                'data' => new ShopResource($shop->load(['account', 'address', 'categories'])),
            ], 201);

        }catch(Exception $e){
            DB::rollback();
            
            return response()->json([
                "success" => false, 
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        return new ShopResource($shop->load(['account', 'address', 'categories']));
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
    public function update(ShopRequest $request, Shop $shop)
    {
        $validated = $request->validated();

        try{

            DB::beginTransaction();

            // if(isset($validated['account_id'])){
            //     $account = Account::find($validated['account_id']);

            //     if(!$account){
            //         throw new Exception("Account not found");
            //     }else{
            //         if(!Shop::isAccountEligible($account)){
            //             throw new Exception("Your account is not eligible for create a shop");
            //         }
            //     }

            //     $shop->account()->sync($validated['account_id']);
            // }            
            
            $shop->update([
                "name" => $validated["name"] ?? $shop->name,
                "reference" => $validated["reference"] ?? $shop->reference,
                "theme_name" => $validated["theme_name"] ?? $shop->theme_name,
                "theme_color" => $validated["theme_color"] ?? $shop->theme_color,
                "status" => $validated["status"] ?? $shop->status,
                "slug" => $validated["slug"] ?? $shop->slug,
                "shop_description" => $validated["shop_description"] ?? $shop->shop_description
            ]);


            DB::commit();

            return response()->json([
                "success" => true,
                'message' => 'Shop update successfully.',
                'data' => new ShopResource($shop->load(['account', 'address', 'categories'])),
            ], 201);

        }catch(Exception $e){
            DB::rollBack();

            return response()->json([
                "success" => false, 
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
