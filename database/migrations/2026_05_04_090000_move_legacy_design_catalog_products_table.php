<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->hasLegacyDesignCatalogTable()) {
            Schema::rename('veara_products', 'design_catalog_products');
        }

        if (Schema::hasTable('veara_product_labels') && ! Schema::hasTable('design_catalog_product_labels')) {
            Schema::rename('veara_product_labels', 'design_catalog_product_labels');
        }

        if (
            Schema::hasTable('design_catalog_product_labels')
            && Schema::hasColumn('design_catalog_product_labels', 'veara_product_id')
            && ! Schema::hasColumn('design_catalog_product_labels', 'design_catalog_product_id')
        ) {
            DB::statement('ALTER TABLE design_catalog_product_labels RENAME COLUMN veara_product_id TO design_catalog_product_id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('design_catalog_product_labels')
            && Schema::hasColumn('design_catalog_product_labels', 'design_catalog_product_id')
            && ! Schema::hasColumn('design_catalog_product_labels', 'veara_product_id')
        ) {
            DB::statement('ALTER TABLE design_catalog_product_labels RENAME COLUMN design_catalog_product_id TO veara_product_id');
        }

        if (Schema::hasTable('design_catalog_product_labels') && ! Schema::hasTable('veara_product_labels')) {
            Schema::rename('design_catalog_product_labels', 'veara_product_labels');
        }

        if (Schema::hasTable('design_catalog_products') && ! Schema::hasTable('veara_products')) {
            Schema::rename('design_catalog_products', 'veara_products');
        }
    }

    private function hasLegacyDesignCatalogTable(): bool
    {
        return Schema::hasTable('veara_products')
            && Schema::hasColumn('veara_products', 'source_labeled_product_id')
            && ! Schema::hasTable('design_catalog_products');
    }
};
