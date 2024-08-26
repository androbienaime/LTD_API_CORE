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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string("firstname")->nullable(true);
            $table->string("lastname")->nullable(true);
            $table->string("middle_name")->nullable(true);
            $table->string("gender")->nullable(true);
            $table->foreignId("user_id")->nullable(true);
            $table->foreignId("address_id")->nullable(true);
            $table->unsignedInteger("identityNumber_id")->nullable(true);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date("date_of_birth")->nullable(true);
            $table->softDeletes();
            $table->boolean("status")->default(1);
            $table->boolean("first_time_login")->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
