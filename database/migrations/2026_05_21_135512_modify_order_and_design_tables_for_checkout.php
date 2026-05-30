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
        // Make designs.printify_product_id nullable (it starts null, set after first order)
        Schema::table('designs', function (Blueprint $table) {
            $table->string('printify_product_id')->nullable()->change();
        });

        // Add printify_order_id to orders (guard against existing column)
        if (!Schema::hasColumn('orders', 'printify_order_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('printify_order_id')->nullable()->after('stripe_payment_id');
            });
        }

        // 3. Add new columns to order_items (each guarded individually)
        if (!Schema::hasColumn('order_items', 'veara_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('veara_product_id')->nullable()->after('design_id');
            });
        }

        if (!Schema::hasColumn('order_items', 'garment_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('garment_variant_id')->nullable()->after('veara_product_id');
            });
        }

        if (!Schema::hasColumn('order_items', 'image')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('image')->nullable()->after('price');
            });
        }

        // 4. Change existing order_items columns — only if they exist
        if (Schema::hasColumn('order_items', 'printify_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('printify_product_id')->nullable()->change();
            });
        }

        if (Schema::hasColumn('order_items', 'printify_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('printify_variant_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->string('printify_product_id')->nullable(false)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('printify_order_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['veara_product_id', 'garment_variant_id', 'image']);
            $table->string('printify_product_id')->nullable(false)->change();
            $table->string('printify_variant_id')->nullable(false)->change();
        });
    }
};
