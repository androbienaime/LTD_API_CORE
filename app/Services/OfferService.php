<?php

namespace App\Services;

use App\Models\Core\Product;
use App\Models\Core\ProductOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OfferService
{
    /**
     * Durée de validité d'une offre acceptée (en minutes).
     * Passé ce délai, le token ne peut plus être utilisé en commande.
     */
    private const OFFER_TTL_MINUTES = 3000;

    // ─── Soumettre une offre ─────────────────────────────────────────────────

    /**
     * Crée une offre "pending" pour un produit donné.
     *
     * Appelé depuis le controller lorsque l'utilisateur clique sur
     * "Faire une offre" sur la fiche produit.
     *
     * @return array{success: bool, data?: array, message?: string, code: int}
     */
    public static function makeOffer(Request $request, Product $product): array
    {
        $validated = $request->validate([
            'offered_price' => [
                'required',
                'numeric',
                'min:0.01',
                // L'offre ne peut pas dépasser le prix affiché (optionnel selon votre logique)
                // 'max:' . $product->price,
            ],
            'note' => 'nullable|string|max:500',
        ]);

        // Vérifier que le produit est disponible avant d'accepter une offre
        if (!$product->isAvailable()) {
            return [
                'success' => false,
                'message' => "Le produit {$product->name} n'est pas disponible.",
                'code'    => 422,
            ];
        }

        $offer = ProductOffer::create([
            'product_id'    => $product->id,
            'user_id'       => auth()->id(), // null si pas d'auth
            'offered_price' => $validated['offered_price'],
            'token'         => self::generateToken(),
            'status'        => 'accepted',   // en attente de validation admin/vendeur
            'note'          => $validated['note'] ?? null,
            'expires_at'    => Carbon::now()->addMinutes(self::OFFER_TTL_MINUTES),
        ]);

        return [
            'success' => true,
            'data'    => [
                'offer_id'      => $offer->id,
                'token'         => $offer->token,
                'offered_price' => $offer->offered_price,
                'status'        => $offer->status,
                'expires_at'    => $offer->expires_at->toIso8601String(),
                'message'       => 'Votre offre a été soumise. Utilisez ce token lors de votre commande une fois acceptée.',
            ],
            'code'    => 201,
        ];
    }

    // ─── Accepter / Rejeter une offre (côté admin/vendeur) ───────────────────

    /**
     * Le vendeur/admin accepte ou rejette une offre.
     * Si acceptée, l'offre peut être utilisée pendant OFFER_TTL_MINUTES.
     */
    public static function respondToOffer(ProductOffer $offer, string $decision): array
    {
        if (!in_array($decision, ['accepted', 'rejected'])) {
            return ['success' => false, 'message' => 'Décision invalide.', 'code' => 422];
        }

        if ($offer->status !== 'pending') {
            return [
                'success' => false,
                'message' => "Cette offre ne peut plus être modifiée (statut : {$offer->status}).",
                'code'    => 422,
            ];
        }

        // Renouveler la fenêtre d'expiration à partir du moment de l'acceptation
        $offer->update([
            'status'     => $decision,
            'expires_at' => $decision === 'accepted'
                ? Carbon::now()->addMinutes(self::OFFER_TTL_MINUTES)
                : $offer->expires_at,
        ]);

        return [
            'success' => true,
            'data'    => ['status' => $offer->status, 'token' => $offer->token],
            'code'    => 200,
        ];
    }

    // ─── Validation lors du placement de commande ────────────────────────────

    /**
     * Valide un token d'offre au moment du placement de la commande.
     *
     * Retourne le prix proposé si tout est valide,
     * lève une \Exception sinon (sera catchée par OrderService).
     *
     * @throws \Exception
     */
    public static function validateAndConsumeOffer(string $token, int $productId): float
    {
        $offer = ProductOffer::where('token', $token)->first();

        if (!$offer) {
            throw new \Exception("Token d'offre invalide : {$token}");
        }

        if ($offer->product_id !== $productId) {
            throw new \Exception("Ce token d'offre ne correspond pas au produit ID {$productId}.");
        }

        if ($offer->status !== 'accepted') {
            throw new \Exception(
                "L'offre (token: {$token}) n'est pas acceptée (statut actuel : {$offer->status})."
            );
        }

        if ($offer->isExpired()) {
            // Marquer comme expiré en base pour la traçabilité
            $offer->update(['status' => 'expired']);
            throw new \Exception("L'offre (token: {$token}) a expiré.");
        }

        // Consommer l'offre : elle ne pourra pas être réutilisée
        $offer->markAsUsed();

        return (float) $offer->offered_price;
    }

    // ─── Interne ─────────────────────────────────────────────────────────────

    private static function generateToken(): string
    {
        return hash('sha256', Str::uuid() . microtime());
    }
}