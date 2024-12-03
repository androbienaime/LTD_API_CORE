<?php

namespace App\Services;

use App\Models\Core\LTSPSEO;
use App\Models\Core\Product;
use App\Models\Core\Currency;
use Illuminate\Support\Facades\DB;
use App\Models\Core\ProductDiscount;
use App\Http\Requests\Core\ProductRequest;
use App\Http\Resources\Core\ProductResource;
use App\Core\States\GeneralStatus\ActiveState;
use Exception;
use Money\Exchange;

class ProductService
{
    public static function storeProduct(ProductRequest $request){
        try {
            DB::beginTransaction();

            // Validation
            $validated = $request->validated();

            $discount = null;
            if (!empty($validated['discount'])) {
                $discount = ProductDiscount::create([
                    'discount' => $validated['discount']['discount'],
                    'description' => $validated['discount']['description'] ?? null,
                    'start_date' => $validated['discount']['start_date'] ?? null,
                    'end_date' => $validated['discount']['end_date'] ?? null,
                ]);
            }

             // Créer ou associer l'objet LTSPSEO si envoyé
            $productSeo = null;
            if (!empty($validated['product_seo'])) {
                $productSeo = LTSPSEO::create([
                    'meta_title' => $validated['product_seo']['meta_title'],
                    'meta_description' => $validated['product_seo']['meta_description'] ?? null,
                    'is_redirection' => $validated['product_seo']['is_redirection'] ?? false,
                    'category_id' => $validated['product_seo']['category_id'] ?? null,
                ]);

                // Ajouter les tags si fournis
                if (!empty($validated['product_seo']['tags'])) {
                    $productSeo->attachTags($validated['product_seo']['tags']);
                }
            }

            // Créer le produit
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'has_unlimited_stock' => $validated['has_unlimited_stock'] ?? false,
                'article' => $validated['article'] ?? null,
                'shop_id' => $validated['shop_id'] ?? null,
                'currency_id' => Currency::findByIsoCode($validated['currency_iso_code'])->id,
                'has_discount' => $validated["has_discount"],
                'product_discount_id' => $discount ? $discount->id : null,
                'ltsp_seo_id' => $productSeo?->id,
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

            return [
                "success" => true,
                "data" => new ProductResource($product),
                "code" => 202
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                "success" => false,
                'message' => 'Une erreur est survenue lors de la création du produit',
                'error' => $e->getMessage(),
                "code" => 500
            ];
        }
    }

    public static function updateProduct(ProductRequest $request, Product $product){
        
        $validated = $request->validated();
    
        try{
            
            DB::beginTransaction();

            // Gestion du ProductDiscount
            if (!empty($validated['discount'])) {
                if ($product->productDiscount) {
                    // Mettre à jour le discount existant
                    $product->productDiscount->update([
                        'discount' => $validated['discount']['discount'],
                        'description' => $validated['discount']['description'] ?? null,
                        'start_date' => $validated['discount']['start_date'] ?? null,
                        'end_date' => $validated['discount']['end_date'] ?? null,
                    ]);
                } else {
                    // Créer un nouveau discount
                    $discount = ProductDiscount::create([
                        'discount' => $validated['discount']['discount'],
                        'description' => $validated['discount']['description'] ?? null,
                        'start_date' => $validated['discount']['start_date'] ?? null,
                        'end_date' => $validated['discount']['end_date'] ?? null,
                    ]);
                    $product->product_discount_id = $discount->id;
                }
            } elseif ($product->productDiscount) {
                // Supprimer le discount si l'entrée est vide
                $product->productDiscount->delete();
                $product->product_discount_id = null;
            }
        
            // Gestion du LTSPSEO
            if (!empty($validated['product_seo'])) {
                if ($product->ltspSeo) {
                    // Mettre à jour le LTSPSEO existant
                    $product->ltspSeo->update([
                        'meta_title' => $validated['product_seo']['meta_title'],
                        'meta_description' => $validated['product_seo']['meta_description'] ?? null,
                        'is_redirection' => $validated['product_seo']['is_redirection'] ?? false,
                        'category_id' => $validated['product_seo']['category_id'] ?? null,
                    ]);
        
                    // Mettre à jour les tags
                    if (!empty($validated['product_seo']['tags'])) {
                        $product->ltspSeo->syncTags($validated['product_seo']['tags']);
                    } else {
                        $product->ltspSeo->detachTags($validated['product_seo']);
                    }
                } else {
                    // Créer un nouveau LTSPSEO
                    $ltspSeo = LTSPSEO::create([
                        'meta_title' => $validated['product_seo']['meta_title'],
                        'meta_description' => $validated['product_seo']['meta_description'] ?? null,
                        'is_redirection' => $validated['product_seo']['is_redirection'] ?? false,
                        'category_id' => $validated['product_seo']['category_id'] ?? null,
                    ]);
        
                    // Ajouter les tags
                    if (!empty($validated['product_seo']['tags'])) {
                        $ltspSeo->attachTags($validated['product_seo']['tags']);
                    }
        
                    $product->ltsp_seo_id = $ltspSeo->id;
                }
            } elseif ($product->ltspSeo) {
                // Supprimer le LTSPSEO si l'entrée est vide
                $product->ltspSeo->delete();
                $product->ltsp_seo_id = null;
            }
        
            // Mise à jour du produit
            $product->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'has_unlimited_stock' => $validated['has_unlimited_stock'] ?? false,
                'article' => $validated['article'] ?? null,
                'shop_id' => $validated['shop_id'] ?? null,
                'currency_id' => Currency::findByIsoCode($validated['currency_iso_code'])->id,
                'has_discount' => $validated['has_discount'],
                'product_discount_id' => $product->product_discount_id,
                'ltsp_seo_id' => $product->ltsp_seo_id,
            ]);
            
            DB::commit();

            return [
                "success" => true,
                "data" => new ProductResource($product->load(['productDiscount', 'ltspSeo.tags'])),
                "code" => 202
            ];
        }catch(Exception $e){
            DB::rollBack();

            return [
                "success" => false,
                "message" => $e->getMessage(),
                "code" => 500
            ];
        }

    }
}
