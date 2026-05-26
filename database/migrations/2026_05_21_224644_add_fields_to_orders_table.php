<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->unsignedBigInteger('payment_method_id')
                ->nullable()
                ->after('id');

            $table->foreign('payment_method_id', 'fk_orders_payment_method')
                ->references('id')
                ->on('payment_methods')
                ->cascadeOnDelete();

            $table->boolean('has_advance')
                ->default(false)
                ->after('payment_method_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign('fk_orders_payment_method');

            $table->dropColumn([
                'payment_method_id',
                'has_advance',
            ]);
        });
    }
};