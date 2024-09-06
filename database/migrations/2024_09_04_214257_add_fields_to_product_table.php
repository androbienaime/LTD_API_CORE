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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId("product_discount_id")->nullable(true);
            

            // Options
            $table->boolean('is_in_stock')->default(true);
            $table->boolean("has_multi_price")->default(0)->nullable();
            $table->boolean('has_unlimited_stock')->default(true);
            $table->boolean('has_discount')->default(false);
            $table->boolean("has_max_cart")->default(0)->nullable();
            $table->bigInteger("min_cart")->nullable()->unsigned();
            $table->bigInteger("max_cart")->nullable()->unsigned();
            $table->boolean("has_stock_alert")->default(0)->nullable();
            $table->bigInteger("min_stock_alert")->nullable()->unsigned();
            $table->bigInteger("max_stock_alert")->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
