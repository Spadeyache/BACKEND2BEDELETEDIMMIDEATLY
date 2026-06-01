<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\DesignCatalog\DesignLabelCatalogSyncService;
use App\Services\DesignCatalog\VearaProductImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('veara:sync-design-labels {--taxonomy=}', function (DesignLabelCatalogSyncService $sync) {
    $path = $this->option('taxonomy') ?: base_path('../veara-design/packages/taxonomy/taxonomy.json');
    $result = $sync->syncFromTaxonomyFile($path);

    $this->info("Synced {$result['groups']} label groups and {$result['labels']} labels.");
})->purpose('Sync the backend unified design label list from the taxonomy JSON file');

Artisan::command('veara:import-products {--connection=veara_design_source} {--limit=100} {--all}', function (VearaProductImportService $importer) {
    $connection = (string) $this->option('connection');

    if ($connection === 'veara_design_source' && !Config::has("database.connections.{$connection}")) {
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

    $result = $importer->importFromConnection(
        $connection,
        (int) $this->option('limit'),
        (bool) $this->option('all'),
    );

    $this->info("Read {$result['read']} labeled products.");
    $this->info("Imported {$result['imported']}, updated {$result['updated']}, attached {$result['attached_labels']} labels.");
})->purpose('Import labeled scraped designs into backend veara_products');
