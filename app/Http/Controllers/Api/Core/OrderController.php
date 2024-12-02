<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\Order;
use App\Models\Core\Product;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Core\OrderRequest;
use App\Services\OrderCalculatorService;
use App\Http\Resources\Core\OrderResource;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return OrderResource::collection(Order::all());

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
    public function store(OrderRequest $request)
    {
       $response = OrderService::createOrder($request);

       return response()->json($response, $response['code'] ?? 404);
    }


    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return new OrderResource($order);
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
    public function update(OrderRequest $request, Order $order)
    {
        if(!$order){
            return response()->json([
                "message" => "Order Not found"
            ], 404);
        }
        
       $response = OrderService::updateOrder($request, $order);

       return response()->json($response, $response['code'] ?? 404);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showState(Order $order){
        if($order == null){
            return response()->json([
                "error" => "Erreur not found"
            ]);
        }


        return response()->json(
            OrderService::showState($order)
        );
    }

    public function changeState(Order $order, Request $request)
    {

        return response()->json(OrderService::changeState($order, $request));
    }

    public function calculateSubTotalLive(Request $request){
        $subTotal = OrderService::calculateSubTotalLive($request);
        return response()->json($subTotal, $subTotal["code"]);
    }

    public function calculateTotalLive(Request $request){
        $total = OrderService::calculateTotalLive($request);
        return response()->json($total, $total["code"]);
    }

}
