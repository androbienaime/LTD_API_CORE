<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // Optionnel : si votre système a des utilisateurs authentifiés
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Le prix proposé par le client (jamais le prix de base du produit)
            $table->decimal('offered_price', 12, 2);

            // Token unique signé, transmis au moment de la commande
            $table->string('token', 64)->unique();

            // Statuts possibles du cycle de vie de l'offre
            $table->enum('status', ['pending', 'accepted', 'rejected', 'used', 'expired'])
                  ->default('pending');

            // Note optionnelle du client (ex: "je propose ce prix car...")
            $table->text('note')->nullable();

            // Date d'expiration de l'offre (ex: 30 min après création)
            $table->timestamp('expires_at');

            $table->timestamps();

            // Index utiles
            $table->index(['product_id', 'status']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_offers');
    }
};