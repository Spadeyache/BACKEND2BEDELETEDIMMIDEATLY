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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['printify_product_id', 'printify_variant_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('veara_product_id');
            $table->unsignedBigInteger('garment_variant_id');
            $table->string('printify_product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['veara_product_id', 'garment_variant_id', 'printify_product_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('printify_product_id');
            $table->string('printify_variant_id');
        });
    }
};
