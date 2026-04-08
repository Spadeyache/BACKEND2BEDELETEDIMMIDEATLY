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
        Schema::create('design_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_render_id');
            $table->string('type', 20);
            $table->string('content');
            $table->double('x_position');
            $table->double('y_position');
            $table->double('width');
            $table->double('height');
            $table->string('font_family');
            $table->float('font_size', 1);
            $table->string('color');
            $table->string('status')->default('active');
            // $table->enum('status', ['active', 'inactive']);
            $table->unsignedTinyInteger('created_by')->nullable();
            $table->unsignedTinyInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_elements');
    }
};
