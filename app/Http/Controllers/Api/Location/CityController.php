<?php

namespace App\Http\Controllers\Api\Location;

use Illuminate\Http\Request;
use App\Models\Location\City;
use App\Models\Location\State;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Location\CityResource;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CityResource::collection(City::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
    
        $city = City::create($request->all());
    
        return response()->json([
            'message' => 'City created successfully',
            'data' => new CityResource($city)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        return new CityResource($city);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
    
        $city = $city->update($request->all());
    
        return response()->json([
            'message' => 'City created successfully',
            'data' => new CityResource($city)
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        $city->delete();

        return response()->json(['message' => 'City deleted successfully.']);
    }

    public function getCitiesByState(State $state)
    {
        try {
            if(!$state){
                throw new \Exception("No state found");
            }

            $cities = Cache::remember("states_country_{$state->id}", 3600, function () use ($state) {
                $cities = $state->cities()->get(); // Chargement des villes
        
                if ($cities->isEmpty()) {
                    throw new \Exception("No cities found for this state."); // Lever une exception si vide
                }
        
                return $cities;
            });
            
            // Renvoyer les villes si elles existent
            return response()->json([
                'success' => true,
                'data' => CityResource::collection($cities)
            ]);
        
        } catch (\Exception $e) {
            // Gérer l'erreur
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
