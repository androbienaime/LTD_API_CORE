<?php

namespace App\Http\Controllers\Api\Core;

use Illuminate\Support\Arr;
use App\Models\Core\Product;
use Illuminate\Http\Request;
use App\Models\Core\Currency;
use App\Services\CurrencyServices;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Core\ProductResource;
use App\Core\States\GeneralStatus\ActiveState;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::getAvailableProductsWithMedia());
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
        try {
            DB::beginTransaction();

            // Validation
            $validated = $this->validatedProduct($request);

            // Créer le produit
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'has_unlimited_stock' => $validated['has_unlimited_stock'] ?? false,
                'article' => $validated['article'] ?? null,
                'shop_id' => $validated['shop_id'] ?? null,
                'currency_id' => $validated['currency_id'],
                'status' => ActiveState::class // Si vous utilisez Spatie State
            ]);

            // Attacher les catégories
            if (!empty($validated['categories'])) {
                $product->categories()->attach($validated['categories']);
            }

            // Attacher les marques
            if (!empty($validated['brands'])) {
                $product->brands()->attach($validated['brands']);
            }

            
            // Créer les déclinaisons avec leurs values
            if (!empty($validated['declinations'])) {
                foreach ($validated['declinations'] as $declinationData) {
                    // Créer la déclinaison
                    $declination = $product->declinations()->create([
                        'name' => $declinationData['name'],
                        'price' => $declinationData['price'],
                        'quantity' => $declinationData['quantity']
                    ]);

                    // Attacher les values à la déclinaison
                    if (!empty($declinationData['values'])) {
                        $declination->values()->attach($declinationData['values']);
                    }
                }
            }

            // Gérer les images supplémentaires
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)
                            ->toMediaCollection('product_images');
                }
            }

            DB::commit();

            // Charger les relations pour la ressource
            $product->load(['categories', 'brands', 'declinations', 'shop', 'currency']);

            return new ProductResource($product);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(Product::find($id)){
            return new ProductResource(Product::find($id));
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
    public function update(Request $request, Product $product)
    {
        try {
            DB::beginTransaction();

            // Validation
            $validated = $this->validatedProduct($request, true);

            // Mettre à jour le produit
            $product->update($validated);

            // Synchroniser les catégories
            if (isset($validated['categories'])) {
                $product->categories()->sync($validated['categories']);
            }

            // Synchroniser les marques
            if (isset($validated['brands'])) {
                $product->brands()->sync($validated['brands']);
            }

            // Gérer les déclinaisons
            if (!empty($validated['declinations'])) {
                // Récupérer les IDs des déclinaisons existantes
                $existingDeclinationIds = collect($validated['declinations'])
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                // Supprimer les déclinaisons qui ne sont plus présentes
                $product->declinations()
                    ->whereNotIn('id', $existingDeclinationIds)
                    ->delete();

                foreach ($validated['declinations'] as $declinationData) {
                    if (isset($declinationData['id'])) {
                        // Mettre à jour la déclinaison existante
                        $declination = $product->declinations()->find($declinationData['id']);
                        if ($declination) {
                            $declination->update([
                                'name' => $declinationData['name'],
                                'price' => $declinationData['price'],
                                'quantity' => $declinationData['quantity']
                            ]);
                            // Synchroniser les values
                            $declination->values()->sync($declinationData['values']);
                        }
                    } else {
                        // Créer une nouvelle déclinaison
                        $declination = $product->declinations()->create([
                            'name' => $declinationData['name'],
                            'price' => $declinationData['price'],
                            'quantity' => $declinationData['quantity']
                        ]);
                        // Attacher les values
                        $declination->values()->attach($declinationData['values']);
                    }
                }
            } else {
                // Si aucune déclinaison n'est fournie, supprimer toutes les déclinaisons existantes
                $product->declinations()->delete();
            }

            // Supprimer les images marquées pour suppression
            if (!empty($validated['remove_images'])) {
                $product->media()
                    ->whereIn('id', $validated['remove_images'])
                    ->delete();
            }

            // Ajouter de nouvelles images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)
                        ->toMediaCollection('product_images');
                }
            }

            // Mettre à jour l'image de couverture si fournie
            if ($request->hasFile('cover_image')) {
                // Supprimer l'ancienne image de couverture si elle existe
                if ($product->getFirstMedia('product_images')) {
                    $product->getFirstMedia('product_images')->delete();
                }
                $product->addMedia($request->file('cover_image'))
                    ->toMediaCollection('product_images');
            }

            DB::commit();

            // Charger les relations pour la ressource
            $product->load([
                'categories', 
                'brands', 
                'declinations.values', 
                'shop', 
                'currency'
            ]);

            return new ProductResource($product);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
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

    private function validatedProduct(Request $request, $updateOneField=false){
        $required = ($updateOneField) ? "sometimes|" : "required|";

        return $request->validate([
            'name' => $required.'string|max:255',
            'slug' => $required.'string|unique:products,slug',
            'description' => 'nullable|string',
            'price' => $required.'numeric|min:0',
            'stock' => $required.'integer|min:0',
            'has_unlimited_stock' => 'boolean',
            'article' => 'string',
            'shop_id' => 'nullable|exists:shops,id',
            'currency_id' => $required.'exists:currencies,id',
            
            // Relations
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'declinations' => 'array',
            'declinations.*.name' => $required.'string',
            'declinations.*.price' => $required.'numeric',
            'declinations.*.quantity' => $required.'numeric',
            'declinations.*.values' => $required.'array',
            'declinations.*.values.*' => 'exists:declination_values,id',

            // Images
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096'
        ]);
    }
}
