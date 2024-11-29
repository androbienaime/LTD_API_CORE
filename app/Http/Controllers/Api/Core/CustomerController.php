<?php

namespace App\Http\Controllers\Api\Core;

use Illuminate\Http\Request;
use App\Models\Core\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CustomerRequest;
use App\Http\Resources\Core\CustomerResource;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerResource::collection(Customer::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        try{

            DB::beginTransaction();

            $validatedData = $request->validated();

            // Créer l'utilisateur sans les adresses
            $customer = Customer::create([
                'firstname' => $validatedData['firstname'],
                'lastname' => $validatedData['lastname'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'email' => $validatedData['email'] ?? null,
                'middle_name' => $validatedData['middle_name'] ?? null,
                'identityNumber_id' => $validatedData["identity_number_id"] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'shop_id' =>$validatedData['shop_id'] ?? null,
                'merchant_id' => $validatedData['merchant_id'] ?? null
            ]);

            if(isset($validatedData["addresses"])){
                // Associer les adresses via la relation belongsToMany
                $customer->addresses()->sync($validatedData['addresses']);
            }

            return response()->json([
                'message' => 'Customer créé avec succès.',
                'customer' => new CustomerResource($customer), // Charger les adresses associées
            ], 201);

            DB::commit();
        }catch(Exception $e){
            DB::rollBack();

            return [
                'message' => 'Error updating customer',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
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
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            // Mettre à jour les informations du client
            $customer->update([
                'firstname' => $validatedData['firstname'] ?? $customer->firstname,
                'lastname' => $validatedData['lastname'] ?? $customer->lastname,
                'gender' => $validatedData['gender'] ?? $customer->gender,
                'email' => $validatedData['email'] ?? $customer->email,
                'middle_name' => $validatedData['middle_name'] ?? $customer->middle_name,
                'identityNumber_id' => $validatedData['identity_number_id'] ?? $customer->identityNumber_id,
                'date_of_birth' => $validatedData['date_of_birth'] ?? $customer->date_of_birth,
                'shop_id' => $validatedData['shop_id'] ?? $customer->shop_id,
                'merchant_id' => $validatedData['merchant_id'] ?? $customer->merchant_id,
            ]);

            // Mettre à jour les adresses, si fournies
            if (isset($validatedData['addresses'])) {
                $customer->addresses()->sync($validatedData['addresses']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Customer mis à jour avec succès.',
                'customer' => new CustomerResource($customer), // Charger les adresses associées
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'message' => 'Error updating customer',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Vérifier si le client a des relations critiques
            if ($customer->orders()->exists()) {
                return response()->json([
                    'message' => "Impossible de supprimer le client ID {$customer->id}. Ce client a des commandes associées."
                ], 400);
            }
    
            // Supprimer les relations associées (exemple: adresses)
            $customer->addressCustomers()->delete();
    
            // Supprimer le client
            $customer->delete();
    
            return response()->json([
                'message' => 'Client supprimé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du client.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
