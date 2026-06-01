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
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('veara_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('source_product_id')->nullable()->unique();
            $table->uuid('source_labeled_product_id')->nullable()->unique();
            $table->string('source_system')->default('veara-design');
            $table->text('source_url')->nullable();
            $table->string('source_domain')->nullable();
            $table->string('title')->nullable();
            $table->text('front_image_url')->nullable();
            $table->text('back_image_url')->nullable();
            $table->text('front_mockup_url')->nullable();
            $table->text('back_mockup_url')->nullable();
            $table->string('price_range')->nullable();
            $table->string('design_type')->nullable();
            $table->string('mood')->nullable();
            $table->jsonb('style_tags')->nullable();
            $table->jsonb('subject_matter')->nullable();
            $table->jsonb('placement')->nullable();
            $table->jsonb('target_audience_guess')->nullable();
            $table->jsonb('seasonal_fit')->nullable();
            $table->jsonb('color_palette')->nullable();
            $table->jsonb('design_labels')->nullable();
            $table->integer('complexity_score')->nullable();
            $table->boolean('text_present')->nullable();
            $table->decimal('pet_relevance_score', 4, 2)->nullable();
            $table->decimal('label_confidence', 4, 3)->nullable();
            $table->string('review_source')->nullable();
            $table->boolean('vectorized')->default(false);
            $table->string('embedding_model')->nullable();
            $table->timestamp('labeled_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['status', 'vectorized']);
            $table->index(['design_type', 'mood']);
            $table->index('source_domain');
        });

        DB::statement('ALTER TABLE veara_products ADD COLUMN embedding vector(160)');
        DB::statement('CREATE INDEX veara_products_embedding_hnsw_idx ON veara_products USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veara_products');
    }
};
