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
        Schema::table('veara_products', function (Blueprint $table) {
            $table->string('title')->nullable()->after('product_id');
            $table->decimal('price', 10, 2)->nullable()->after('title');
            $table->text('description')->nullable()->after('price');
        });

        // Backfill existing rows so NOT NULL constraint can be applied
        \DB::table('veara_products')->whereNull('title')->update([
            'title' => 'Untitled Product',
            'price' => 0.00,
        ]);

        Schema::table('veara_products', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('veara_products', function (Blueprint $table) {
            $table->dropColumn(['title', 'price', 'description']);
        });
    }
};
