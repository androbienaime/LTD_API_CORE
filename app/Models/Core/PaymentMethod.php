<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description"
    ];


    public static function findOrCreatePaymentMethod(string|int $identifier): string
    {
        // Si c'est un nombre → rechercher par id
        if (is_numeric($identifier)) {
            $paymentMethod = self::findOrFail($identifier);
            return $paymentMethod->id;
        }

        // Si c'est une string → vérifier longueur minimale
        if (strlen(trim($identifier)) < 2) {
            throw new \InvalidArgumentException('Le nom du moyen de paiement doit contenir au moins 2 caractères.');
        }

        // Trouver ou créer par name
        $paymentMethod = self::firstOrCreate(
            ['name' => trim($identifier)],
            ['description' => null]
        );

        return $paymentMethod->id;
    }

}
