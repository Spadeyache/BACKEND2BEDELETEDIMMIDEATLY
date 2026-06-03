<?php

namespace App\Services\DesignCatalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DesignCatalogProduct;
use Throwable;

class DesignCatalogAutoImportService
{
    public function __construct(
        private readonly DesignLabelCatalogSyncService $labelSync,
        private readonly VearaProductImportService $productImport,
    ) {
    }

    public function maybeRun(bool $force = false): void
    {
        if (! filter_var(env('VEARA_DESIGN_CATALOG_AUTO_IMPORT', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $limit = max(1, (int) env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_LIMIT', 10));

        if (! $force && DesignCatalogProduct::query()->count() >= $limit) {
            return;
        }

        $ttlSeconds = max(1, (int) env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_TTL', 60));
        $cacheKey = 'veara_design_catalog_auto_import:last_run';

        if (! $force && ! Cache::add($cacheKey, now()->toISOString(), $ttlSeconds)) {
            return;
        }

        try {
            $taxonomyPath = env('VEARA_DESIGN_TAXONOMY_PATH') ?: base_path('../veara-design/packages/taxonomy/taxonomy.json');
            if (
                filter_var(env('VEARA_DESIGN_CATALOG_AUTO_SYNC_LABELS', false), FILTER_VALIDATE_BOOLEAN)
                && is_file($taxonomyPath)
                && Cache::add('veara_design_catalog_auto_import:labels_synced', now()->toISOString(), 86400)
            ) {
                $this->labelSync->syncFromTaxonomyFile($taxonomyPath);
            }

            $connection = env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_CONNECTION', 'veara_design_source');
            $this->ensureDesignSourceConnection($connection);

            $this->productImport->importFromConnection(
                $connection,
                $limit,
                filter_var(env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_ALL', true), FILTER_VALIDATE_BOOLEAN),
                filter_var(env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_RANDOM', true), FILTER_VALIDATE_BOOLEAN),
                filter_var(env('VEARA_DESIGN_CATALOG_AUTO_IMPORT_ONLY_NEW', true), FILTER_VALIDATE_BOOLEAN),
            );

        } catch (Throwable $exception) {
            Log::warning('Design catalog auto-import failed', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureDesignSourceConnection(string $connection): void
    {
        if ($connection !== 'veara_design_source' || Config::has("database.connections.{$connection}")) {
            return;
        }

        Config::set("database.connections.{$connection}", [
            'driver' => 'pgsql',
            'host' => env('VEARA_DESIGN_DB_HOST', '127.0.0.1'),
            'port' => env('VEARA_DESIGN_DB_PORT', '54322'),
            'database' => env('VEARA_DESIGN_DB_DATABASE', 'postgres'),
            'username' => env('VEARA_DESIGN_DB_USERNAME', 'postgres'),
            'password' => env('VEARA_DESIGN_DB_PASSWORD', 'postgres'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('VEARA_DESIGN_DB_SCHEMA', 'public'),
            'sslmode' => env('VEARA_DESIGN_DB_SSLMODE', 'prefer'),
        ]);

        DB::purge($connection);
    }
}
