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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal("order_amount", 16, 5);
            $table->foreignId("user_id")->nullable(true);
            $table->foreignId("customer_id")->nullable(true);
            $table->foreignId("status_id")->nullable(true);
            $table->foreignId("advance_order_id")->nullable(true);
            $table->decimal("total_amount_order", 16, 5);
            $table->string("reference_order");
            $table->string("secure_key");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
