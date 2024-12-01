<?php

namespace App\Http\Controllers\Api\Core;

use Exception;
use Illuminate\Http\Request;
use App\Models\Core\Currency;
use App\Http\Controllers\Controller;
use App\Http\Resources\Core\CurrencyResource;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CurrencyResource::collection(Currency::all()->where("is_active", true));
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
    public function show(Currency $currency)
    {  
        if(!$currency->is_active){
            return response()->json([
                "success" => false,
                "message" => "The currency you give is not activated"
            ]);
        }
        
        return new CurrencyResource($currency);
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

    public function getCurrencyByCode(String $code){
        try{
            $currency = Currency::where("iso_code", $code)->first();
            if(!$currency){
                throw new Exception("Error : iso code incorrect");
            }

            return $this->show($currency);
        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
}
