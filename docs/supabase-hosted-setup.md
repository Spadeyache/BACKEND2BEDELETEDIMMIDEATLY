# Supabase Hosted Setup

This guide moves VEARA backend data from local Supabase/Postgres to hosted Supabase.

## What Moves To Supabase

Supabase hosted should own:

- PostgreSQL data for backend tables.
- PostgreSQL data for scrape/label source tables, if `veara-design` writes to the same hosted project.
- Storage objects for copied design images.

GitHub still owns only code. It does not store database rows or image files.

## Backend Database

Copy `.env.supabase.example` to `.env` on the hosted backend server and fill the real values from Supabase Dashboard.

Use the Supabase Dashboard connection string from:

```txt
Project Settings -> Database -> Connect
```

Recommended backend values:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-your-region.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.your-project-ref
DB_PASSWORD=your-supabase-database-password
DB_SCHEMA=public
DB_SSLMODE=require
```

Use the Session Pooler for hosted Laravel apps unless you deliberately need a direct connection.

## Design Source Database

If scraping and labeling write to the same hosted Supabase project, point `VEARA_DESIGN_DB_*` to the same database:

```env
VEARA_DESIGN_DB_HOST=aws-0-your-region.pooler.supabase.com
VEARA_DESIGN_DB_PORT=5432
VEARA_DESIGN_DB_DATABASE=postgres
VEARA_DESIGN_DB_USERNAME=postgres.your-project-ref
VEARA_DESIGN_DB_PASSWORD=your-supabase-database-password
VEARA_DESIGN_DB_SCHEMA=public
VEARA_DESIGN_DB_SSLMODE=require
```

If the design pipeline uses a separate Supabase project, use that project's connection values instead.

## Storage Bucket

Create a Supabase Storage bucket:

```txt
design-catalog
```

Then create S3 access keys in Supabase Storage settings and fill:

```env
FILESYSTEM_DISK=supabase
SUPABASE_PROJECT_REF=your-project-ref
SUPABASE_STORAGE_BUCKET=design-catalog
SUPABASE_STORAGE_REGION=auto
SUPABASE_STORAGE_ENDPOINT=https://your-project-ref.storage.supabase.co/storage/v1/s3
SUPABASE_STORAGE_ACCESS_KEY_ID=your-storage-s3-access-key
SUPABASE_STORAGE_SECRET_ACCESS_KEY=your-storage-s3-secret-key
SUPABASE_STORAGE_PUBLIC_URL=https://your-project-ref.supabase.co/storage/v1/object/public/design-catalog
SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT=true
```

The backend stores image files in Supabase Storage and keeps only paths/URLs in PostgreSQL.

## First Deploy Commands

On the hosted backend server:

```bash
php artisan key:generate
php artisan migrate
php artisan veara:sync-design-labels
php artisan veara:import-products --all --limit=50
```

If data already exists in local Supabase and must be moved to hosted Supabase, dump and restore the database instead of relying only on GitHub.

## Data Ownership

```txt
veara-design
  scrape + label
  writes source rows to Supabase Postgres

veara-backend
  imports source rows
  owns design_catalog_products
  copies images to Supabase Storage

veara-website
  reads only backend API
```
