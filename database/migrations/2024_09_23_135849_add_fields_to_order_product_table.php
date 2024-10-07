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
        Schema::table('order_product', function (Blueprint $table) {
            $table->foreignId('declination_id')->nullable(true);
            $table->decimal("sub_totals", 16, 5)->default(0);
            $table->decimal("discount", 16, 5)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropColumn('declination_id');
            $table->dropColumn('sub_totals');
            $table->dropColumn('discount');
        });
    }
};
