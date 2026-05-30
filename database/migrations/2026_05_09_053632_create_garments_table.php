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
        Schema::create('garments', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('category');
            $table->integer('blueprint_id');
            $table->integer('print_provider_id');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        // $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garments');
    }
};
