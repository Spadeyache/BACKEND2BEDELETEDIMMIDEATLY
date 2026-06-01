# VEARA Design Data Flow

This document is the shared map for scraped designs, AI labels, backend storage, admin pages, and website APIs.

## Goal

The website should not depend directly on the scraping database. Scraping and labeling can evolve in `veara-design`, but the website and admin should read one backend-owned format from `veara-backend`.

## Canonical Flow

```txt
veara-design scraper
  -> scraped_products
  -> labeler
  -> ai_labeled_products
  -> vectorizer
  -> ai_labeled_products.embedding
  -> backend import command
  -> veara-backend PostgreSQL
       design_label_groups
       design_labels
       veara_products
       veara_product_labels
  -> Laravel API
  -> veara-website
```

## Ownership

`veara-design` owns pipeline work:

- discovering source products
- scraping images and source URLs
- AI labeling
- vectorizing labels

`veara-backend` owns product data used by the app:

- unified label list
- normalized product rows
- admin visibility
- website API contract
- cart/order references to backend product IDs

`veara-website` owns presentation:

- design grid
- filters
- product/detail UI
- future recommendation UI

## Backend Tables

### `design_label_groups`

One row per label family.

Examples:

- `design_type`
- `mood`
- `style_tags`
- `subject_matter`
- `color_palette.colors`
- `design_labels.composition`

### `design_labels`

One row per allowed label value.

Examples:

- group `mood`, key `cute`
- group `style_tags`, key `streetwear`
- group `subject_matter`, key `pet_cat`

This is the backend's unified label list. Admin and website filters should use this table instead of hard-coded frontend lists.

### `veara_products`

The backend-owned product format for scraped/labeled designs.

Important IDs:

- `id`: backend product ID used by website/admin
- `source_product_id`: original `scraped_products.id`
- `source_labeled_product_id`: original `ai_labeled_products.id`

Important fields:

- `title`
- `front_image_url`
- `back_image_url`
- `source_url`
- `source_domain`
- `design_type`
- `mood`
- `style_tags`
- `subject_matter`
- `color_palette`
- `design_labels`
- `embedding`
- `status`

### `veara_product_labels`

Join table between `veara_products` and `design_labels`.

Use this for filters and future recommendation/ranking features.

## ID Rules

Do not mix these meanings:

```txt
design_id
  Laravel custom design ID from designs.id

veara_product_id
  Backend product ID from veara_products.id

printify_product_id
  Printify product ID
```

If a user customizes a scraped design, store both:

```txt
designs.id           -> custom user design
designs.veara_product_id -> original VEARA scraped/labeled product
```

## Commands

Sync the backend unified label list from the taxonomy JSON:

```bash
php artisan veara:sync-design-labels
```

Override the taxonomy path:

```bash
php artisan veara:sync-design-labels --taxonomy=/absolute/path/to/taxonomy.json
```

Import accepted labeled designs from the pipeline database:

```bash
php artisan veara:import-products --limit=100
```

By default the import reads only rows where `proceed_to_vectorizing = true`.

Import all labeled rows, including pending/rejected review states:

```bash
php artisan veara:import-products --all
```

## Source DB Environment

The import command uses a dynamic PostgreSQL connection named `veara_design_source`.

Defaults target local Supabase:

```env
VEARA_DESIGN_DB_HOST=127.0.0.1
VEARA_DESIGN_DB_PORT=54322
VEARA_DESIGN_DB_DATABASE=postgres
VEARA_DESIGN_DB_USERNAME=postgres
VEARA_DESIGN_DB_PASSWORD=postgres
VEARA_DESIGN_DB_SCHEMA=public
VEARA_DESIGN_DB_SSLMODE=prefer
```

## Public API Contract

Website-facing endpoints:

```txt
GET /api/v1/design-catalog/products
GET /api/v1/design-catalog/products/{id}
GET /api/v1/design-catalog/labels
```

The website should build design filters from `/labels` and render design cards from `/products`.

## Current Implementation Boundary

Implemented now:

- backend schema for unified labels and VEARA products
- label sync command
- product import command
- read-only API for products and labels

Still to add later:

- admin UI for managing labels and imported VEARA products
- website hooks/components that consume the new API
- recommendation endpoint using `embedding`
- cart/checkout validation cleanup around `design_id` vs `veara_product_id`
