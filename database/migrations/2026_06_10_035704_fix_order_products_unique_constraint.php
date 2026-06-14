<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            // 1. Ajout de la colonne product_offer_id si elle n'existe pas encore
            if (!Schema::hasColumn('order_product', 'product_offer_id')) {
                $table->unsignedBigInteger('product_offer_id')->nullable()->after('product_id');
            }

            // 2. Ajout de offer_price si elle n'existe pas encore
            if (!Schema::hasColumn('order_product', 'offer_price')) {
                $table->decimal('offer_price', 10, 2)->nullable()->after('product_offer_id');
            }
        });

        // Séparé pour éviter les conflits de transaction sur les index
        Schema::table('order_product', function (Blueprint $table) {
            // 3. Supprimer l'ancien index unique (order_id, product_id) — nom auto-généré par Laravel
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('order_product');

            foreach ($indexes as $name => $index) {
                $cols = $index->getColumns();
                sort($cols);
                $expected = ['order_id', 'product_id'];
                if ($cols === $expected && $index->isUnique()) {
                    $table->dropUnique($name);
                    break;
                }
            }

            // 4. Nouvel index unique composite
            $table->unique(
                ['order_id', 'product_id', 'offer_price'],
                'order_products_unique_offer'
            );

            // 5. Clé étrangère sur product_offer_id
            $table->foreign('product_offer_id')
                  ->references('id')
                  ->on('product_offers')
                  ->onDelete('set null'); // set null car la colonne est nullable
        });
    }

    public function down(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            // 1. Supprimer la clé étrangère
            $table->dropForeign(['product_offer_id']);

            // 2. Supprimer le nouvel index unique composite
            $table->dropUnique('order_products_unique_offer');

            // 3. Remettre l'ancien index unique (order_id, product_id)
            $table->unique(['order_id', 'product_id']);

            // 4. Supprimer les colonnes ajoutées
            $table->dropColumn(['product_offer_id', 'offer_price']);
        });
    }
};