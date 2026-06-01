<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->isPostgres()) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        }

        Schema::create('veara_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('veara_front');
            $table->string('veara_back');
            $table->string('front_mockup');
            $table->string('back_mockup');
            $table->text('style_tags');
            $table->jsonb('color_palette');
            $table->string('design_type');
            $table->text('subject_matter');
            $table->string('mood');
            $table->integer('complexity_score');
            $table->decimal('pet_relevance_score', 3, 2)->default(0.00);
            $table->text('target_audience_guess');
            $table->text('seasonal_fit');
            // $table->string('embedding'); // vector type, need POSTGRE!!
            $table->string('embedding_model');
            $table->dateTime('labeled_at');
            $table->text('embedding')->nullable();
            $table->timestamps();
        });

        if ($this->isPostgres()) {
            DB::statement('ALTER TABLE veara_products ALTER COLUMN embedding TYPE vector(768) USING embedding::vector');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veara_products');
    }

    private function isPostgres(): bool
    {
        return Schema::getConnection()->getDriverName() === 'pgsql';
    }
};
