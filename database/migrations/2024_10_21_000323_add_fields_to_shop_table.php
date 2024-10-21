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
        Schema::table('shops', function (Blueprint $table) {
               //SEO FIELDS
               $table->foreignId("ltsp_seo_id")->nullable(true);
               $table->string('slug')->unique();
   
                // Shop Information
                $table->text('shop_description')->nullable();
                $table->string('types')->nullable(true);
   
               //  // Performance Tracking
               //  $table->unsignedBigInteger('view_count')->default(0);
               //  $table->unsignedBigInteger('total_sales')->default(0);
               //  $table->float('average_rating', 3, 2)->default(0); // Max rating is 5.00
               //  $table->unsignedBigInteger('review_count')->default(0);
               //  $table->float('conversion_rate', 5, 2)->default(0.00); // Percentage like 12.34
                
               //  // User Interactions
               //  $table->unsignedBigInteger('customer_favorites_count')->default(0);
               //  $table->unsignedBigInteger('share_count')->default(0);
               //  $table->timestamp('last_visited_at')->nullable();
               //  $table->float('engagement_rate', 5, 2)->default(0.00); // Percentage like 12.34
                
               //  // Marketing Tracking
               //  $table->string('utm_source')->nullable();
               //  $table->string('utm_medium')->nullable();
               //  $table->string('utm_campaign')->nullable();
                
               //  // Promotions & Offers
               //  $table->string('discount_code')->nullable();
               //  $table->dateTime('promo_start_date')->nullable();
               //  $table->dateTime('promo_end_date')->nullable();
                
   
                // Location
                $table->foreignId('address_id')->nullable()->constrained()->onDelete('cascade');
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['ltsp_seo_id']);
            $table->dropColumn('ltsp_seo_id');  
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
            $table->dropColumn('types');
            $table->dropColumn('shop_description');
            $table->dropColumn('slug');
        });
    }
};
