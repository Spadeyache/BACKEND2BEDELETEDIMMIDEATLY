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
        //
        Schema::table('order_items', function (Blueprint $table) {
            //
            $table->string('design_id')->nullable()->change();
            $table->string('printify_product_id')->nullable()->change();
            $table->string('printify_variant_id')->nullable()->change();
            $table->string('veara_product_id')->nullable()->after('design_id');
        });

        Schema::table('designs', function (Blueprint $table) {
            //
            $table->string('printify_product_id')->nullable()->change();
            $table->string('printify_variant_id')->nullable()->change();
            $table->string('product_name')->nullable()->change();
            $table->string('product_size')->nullable()->change();
            $table->string('product_color')->nullable()->change();
            $table->string('veara_product_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('order_items', function (Blueprint $table) {
            //
            $table->string('design_id')->nullable(false)->change();
            $table->string('printify_product_id')->nullable(false)->change();
            $table->string('printify_variant_id')->nullable(false)->change();
            $table->dropColumn('veara_product_id');
        });

        Schema::table('designs', function (Blueprint $table) {
            //
            $table->string('printify_product_id')->nullable(false)->change();
            $table->string('printify_variant_id')->nullable(false)->change();
            $table->string('product_name')->nullable(false)->change();
            $table->string('product_size')->nullable(false)->change();
            $table->string('product_color')->nullable(false)->change();
            $table->dropColumn('veara_product_id');
        });
    }
};
