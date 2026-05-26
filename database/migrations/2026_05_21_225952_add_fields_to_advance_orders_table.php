<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_order', function (Blueprint $table) {

            $table->uuid('order_id')
                ->nullable()
                ->after('id');

            $table->unsignedBigInteger('payment_method_id')
                ->nullable()
                ->after('order_id');

            $table->foreign('order_id', 'fk_advance_order_order')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('payment_method_id', 'fk_advance_order_payment_method')
                ->references('id')
                ->on('payment_methods')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('advance_order', function (Blueprint $table) {

            $table->dropForeign('fk_advance_order_order');
            $table->dropForeign('fk_advance_order_payment_method');

            $table->dropColumn([
                'order_id',
                'payment_method_id',
            ]);
        });
    }
};