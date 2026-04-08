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
        Schema::create('design_renders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_id');
            $table->string('area_name');
            $table->string('image_url');
            $table->unsignedTinyInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_renders');
    }
};
