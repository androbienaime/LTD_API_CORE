<?php

namespace App\Http\Controllers\Api\Location;

use Illuminate\Http\Request;
use App\Models\Location\State;
use App\Models\Location\Country;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Location\StateResource;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return StateResource::collection(State::with("cities")->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
    
        $country = State::create($request->all());
    
        return response()->json([
            'message' => 'State created successfully',
            'data' => new StateResource($country)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(State $state)
    {
        return new StateResource($state);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
    
        $state = $state->update($request->all());
    
        return response()->json([
            'message' => 'Country created successfully',
            'data' => new StateResource($state)
        ], 201);
    }

    public function getStatesByCountry(Country $country)
    {
        
        $states = Cache::remember("states_country_{$country->id}", 3600, function () use ($country) {
            return $country->states()->get();
        });
        
        return StateResource::collection($states);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(State $state)
    {
        $state->delete();

        return response()->json(['message' => 'State deleted successfully.']);
    }
}
