<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Models\Core\Product;
use App\Models\Core\ProductOffer;
use App\Models\Core\Shop;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /**
     * POST /products/{product:slug}/offers
     *
     * Le client soumet une offre de prix sur un produit.
     * Retourne un token à conserver pour la commande.
     */
    public function store(Request $request, Shop $shop, Product $product): JsonResponse
    {
        $result = OfferService::makeOffer($request, $product);

        return response()->json(
            ['success' => $result['success'], 'data' => $result['data'] ?? null, 'message' => $result['message'] ?? null],
            $result['code']
        );
    }

    /**
     * PATCH /offers/{offer}/respond
     *
     * L'admin / le vendeur accepte ou rejette une offre.
     * Body JSON attendu : { "decision": "accepted" | "rejected" }
     */
    public function respond(Request $request, ProductOffer $offer): JsonResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:accepted,rejected',
        ]);

        $result = OfferService::respondToOffer($offer, $validated['decision']);

        return response()->json(
            ['success' => $result['success'], 'data' => $result['data'] ?? null, 'message' => $result['message'] ?? null],
            $result['code']
        );
    }

    /**
     * GET /offers/{token}/status
     *
     * Le client vérifie l'état de son offre par token.
     */
    public function status(string $token): JsonResponse
    {
        $offer = ProductOffer::where('token', $token)->first();

        if (!$offer) {
            return response()->json(['success' => false, 'message' => 'Offre introuvable.'], 404);
        }

        // Expiration automatique si dépassée
        if ($offer->status === 'accepted' && $offer->isExpired()) {
            $offer->update(['status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'status'        => $offer->status,
                'offered_price' => $offer->offered_price,
                'expires_at'    => $offer->expires_at->toIso8601String(),
                'product_id'    => $offer->product_id,
            ],
        ]);
    }
}