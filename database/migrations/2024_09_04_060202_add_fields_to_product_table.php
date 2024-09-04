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
            $table->foreignId("ltsp_seo_id")->nullable(true);
            $table->string("sku")->nullable(true);
            $table->boolean("is_trend")->default(true);
            $table->boolean("is_draft")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ltsp_seo_id');
            $table->dropColumn('sku');
            $table->dropColumn('is_trend');
            $table->dropColumn('is_draft');
        });
    }
};
