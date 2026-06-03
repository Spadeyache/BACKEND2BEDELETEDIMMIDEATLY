<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enum\Role;
use App\Models\User;
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

Artisan::command('veara:sync-admin-user', function () {
    $email = env('VEARA_ADMIN_EMAIL', 'admin@admin.com');
    $password = env('VEARA_ADMIN_PASSWORD');

    if (blank($password) || strlen($password) < 12) {
        $this->error('VEARA_ADMIN_PASSWORD must be set to at least 12 characters.');
        return 1;
    }

    $admin = User::updateOrCreate([
        'email' => $email,
    ], [
        'first_name' => env('VEARA_ADMIN_FIRST_NAME', 'admin'),
        'last_name' => env('VEARA_ADMIN_LAST_NAME', 'admin'),
        'phone' => env('VEARA_ADMIN_PHONE', '0123456789'),
        'password' => Hash::make($password),
        'role' => Role::ADMIN->value,
    ]);

    $this->info("Admin user synced: {$admin->email}");
    return 0;
})->purpose('Create or update the admin user from VEARA_ADMIN_* environment variables');

Artisan::command('veara:import-products {--connection=veara_design_source} {--limit=100} {--all} {--random} {--only-new}', function (VearaProductImportService $importer) {
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
        (bool) $this->option('random'),
        (bool) $this->option('only-new'),
    );

    $this->info("Read {$result['read']} labeled products.");
    $this->info("Imported {$result['imported']}, updated {$result['updated']}, attached {$result['attached_labels']} labels.");
})->purpose('Import labeled scraped designs into backend design_catalog_products');
