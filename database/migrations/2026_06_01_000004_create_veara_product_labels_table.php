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
        Schema::create('design_catalog_product_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_catalog_product_id')->constrained('design_catalog_products')->cascadeOnDelete();
            $table->foreignId('design_label_id')->constrained()->cascadeOnDelete();
            $table->string('group_key');
            $table->string('label_key');
            $table->timestamps();

            $table->unique(['design_catalog_product_id', 'design_label_id'], 'design_catalog_product_labels_unique');
            $table->index(['group_key', 'label_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_catalog_product_labels');
    }
};
