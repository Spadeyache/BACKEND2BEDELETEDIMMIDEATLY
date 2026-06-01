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
        Schema::create('garment_variants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('garment_id');
            $table->string('printify_variant_id');
            $table->text('size');
            $table->text('color');
            $table->text('color_hex')->nullable();
            $table->text('blank_mockup_url');
            $table->integer('price_cents');
            $table->boolean('is_enabled')->default(true);
            $table->integer('display_order')->default(0);
            // $table->unique(['garment_id', 'printify_variant_id']);
            $table->timestampsTz();
        });

        // $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garment_variants');
    }
};
