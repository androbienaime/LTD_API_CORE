<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\ShopRequest;
use App\Http\Resources\Core\ShopResource;
use App\Models\Core\Shop;
use Illuminate\Http\Request;

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

        $shop = Shop::create($validated);
        $shop->account()->sync($validated['account_ids']);

        return response()->json([
            'message' => 'Shop created successfully.',
            'data' => new ShopResource($shop->load(['account', 'address', 'categories'])),
        ], 201);
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
