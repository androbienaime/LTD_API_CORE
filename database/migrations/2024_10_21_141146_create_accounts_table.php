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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // account info
            $table->string('firstname');
            $table->string("lastname")->nullable();
            $table->string("username")->unique();
            $table->date("date_of_birth")->nullable();
            $table->string("gender")->nullable();
            
            // account connection
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->timestamp('otp_activated_at')->nullable();
            $table->timestamp('otp_expired_at')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->longText('agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string("host")->nullable();
            $table->string("remember_token")->nullable();
            $table->string("loginBy")->default("email");

            // account status   
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean("is_login")->default(false);
            $table->boolean("is_notification_active")->default(true);

            $table->string("lang")->nullable(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
