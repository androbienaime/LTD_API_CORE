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
        Schema::create('ltsp_seos', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title')->nullable(true);
            $table->string('meta_description')->nullable(true);
            $table->boolean('is_redirection')->default(false);
            $table->foreignId('category_id')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ltsp_seos');
    }
};
