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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId("shop_id")->nullable()->constrained();
            $table->foreignId("account_id")->nullable()->constrained();
        });

        Schema::table("products", function (Blueprint $table) {
            $table->unsignedBigInteger("merchant_id")->nullable();
        });

        Schema::table("categories", function (Blueprint $table) {
            $table->unsignedBigInteger("merchant_id")->nullable();
            $table->foreignId("shop_id")->nullable()->constrained();
        });

        Schema::table("attributes", function (Blueprint $table) {
            $table->unsignedBigInteger("merchant_id")->nullable();
            $table->foreignId("shop_id")->nullable()->constrained();
        });

        Schema::table("brands", function (Blueprint $table) {
            $table->unsignedBigInteger("merchant_id")->nullable();
            $table->foreignId("shop_id")->nullable()->constrained();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId("shop_id")->nullable()->constrained();
            $table->unsignedBigInteger("merchant_id")->nullable();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId("shop_id")->nullable()->constrained();
            $table->string("type")->default("account");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_shop_id_foreign');
            $table->dropForeign('orders_account_id_foreign');

            $table->dropColumn('shop_id');
            $table->dropColumn('account_id');
        });

        Schema::table("products", function (Blueprint $table) {
//            $table->dropForeign('products_merchant_id_foreign');

            $table->dropColumn('merchant_id');
        });

        Schema::table("categories", function (Blueprint $table) {
//            $table->dropForeign('categories_merchant_id_foreign');
            $table->dropForeign('categories_shop_id_foreign');

            $table->dropColumn('shop_id');
            $table->dropColumn('merchant_id');
        });

        Schema::table("attributes", function (Blueprint $table) {
//            $table->dropForeign('attributes_merchant_id_foreign');
            $table->dropForeign('attributes_shop_id_foreign');

            $table->dropColumn('shop_id');
            $table->dropColumn('merchant_id');
        });

        Schema::table("brands", function (Blueprint $table) {
//            $table->dropForeign('brands_merchant_id_foreign');
            $table->dropForeign('brands_shop_id_foreign');

            $table->dropColumn('shop_id');
            $table->dropColumn('merchant_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customers_shop_id_foreign');
//            $table->dropForeign("customers_merchant_id_foreign");


            $table->dropColumn('shop_id');
            $table->dropColumn('merchant_id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign('accounts_shop_id_foreign');
            $table->dropColumn("shop_id");
            $table->dropColumn("type");
        });
    }
};
