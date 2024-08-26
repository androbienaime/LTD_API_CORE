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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->double("width")->nullable(true);
            $table->double("heigth")->nullable(true);
            $table->double("depth")->nullable(true);
            $table->double("weigth")->nullable(true);
            $table->decimal("costs", 16, 5)->default(0);
            $table->string("delivery_mode")->nullable(true);
            $table->foreignId("address_id")->nullable(true)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
