<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Product details
            $table->string("name");
            $table->string("slug")->unique();
            $table->string("description")->nullable(true);
            $table->string("article")->nullable(true);
            $table->string("product_type")->default("product");
            $table->string("sku")->nullable(true);

            // Price
            $table->decimal("price", 16, 5);
            $table->foreignId("currency_id")->constrained();
            $table->decimal("purchase_price", 16, 5)->nullable(true);

            // stock
            $table->integer("stock_quantity")->default(0);
            $table->bigInteger("min_stock_alert")->nullable()->unsigned();
            $table->bigInteger("max_stock_alert")->nullable()->unsigned();

            // cart
            $table->bigInteger("min_cart")->nullable()->unsigned();
            $table->bigInteger("max_cart")->nullable()->unsigned();

            //options
            $table->boolean("is_downloadable")->default(false);
            $table->boolean("is_available_market")->default(false);
            $table->boolean("has_declination")->default(false);
            $table->string("status"); // [active, draft, inactive, suspended, bloked]
            $table->boolean("is_downloaddable")->default(false);
            $table->boolean("is_trend")->default(true);
            $table->boolean('is_in_stock')->default(true);
            $table->boolean("has_multi_price")->default(0)->nullable();
            $table->boolean('has_unlimited_stock')->default(true);
            $table->boolean('has_discount')->default(false);
            $table->boolean("has_max_cart")->default(0)->nullable();
            $table->boolean("has_stock_alert")->default(0)->nullable();

            $table->json("status_data")->nullable();
            $table->foreignId("shop_id")->nullable(true)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
