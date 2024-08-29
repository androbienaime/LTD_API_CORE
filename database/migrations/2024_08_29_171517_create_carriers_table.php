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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string("carrier_name");
            $table->string("transit_time")->nullable(true);
            $table->string("speed_grade")->nullable(true);
            $table->string("logo")->nullable(true);
            $table->string("tracking_url")->nullable(true);
            $table->boolean("free_shipping")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
