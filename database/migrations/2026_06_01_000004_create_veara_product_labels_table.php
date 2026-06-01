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
        Schema::create('veara_product_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veara_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('design_label_id')->constrained()->cascadeOnDelete();
            $table->string('group_key');
            $table->string('label_key');
            $table->timestamps();

            $table->unique(['veara_product_id', 'design_label_id']);
            $table->index(['group_key', 'label_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veara_product_labels');
    }
};
