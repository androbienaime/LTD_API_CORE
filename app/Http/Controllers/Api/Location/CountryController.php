<?php

namespace App\Http\Controllers\Api\Location;

use Illuminate\Http\Request;
use App\Models\Location\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\Location\CountryResource;
use Exception;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CountryResource::collection(Country::with('states.cities')->get());

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:countries,name',
            'iso_code' => 'required|string|size:3|unique:countries,iso_code',
        ]);
    
        $country = Country::create($request->all());
    
        return response()->json([
            'message' => 'Country created successfully',
            'data' => new CountryResource($country)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country)
    {
        return new CountryResource($country->load('states.cities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'sometimes|string|unique:countries,name,' . $country->id,
            'code' => 'sometimes|string|size:3|unique:countries,iso_code,' . $country->id,
        ]);
    
        $country->update($request->all());
    
        return response()->json([
            'message' => 'Country updated successfully',
            'data' => new CountryResource($country)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return response()->json(['message' => 'Country deleted successfully.']);
    }

    public function getCountryByCode(string $code){
        try{
            $country = Country::where("code", $code)->first();
            if(!$country){
                throw new Exception("Erreur iso code incorrect");
            }

            return $this->show($country);
        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
}
