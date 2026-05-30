# MERGE_DIFF.md — Phase 1 Inventory Diff

**Generated:** 2026-05-30
**Source of truth:** `../Admin Dashboard/` (Admin Dashboard)
**Target:** `veara-backend/` (this project, branch `main`, base commit `38826ee`)

This document is a **report only**. No files have been copied or modified by Phase 1
(other than creating this file). Copying happens in Phase 2 (NEW) and Phase 3 (DIFFERENT/merge).

---

## Scope of comparison

Compared trees (recursively), by relative path:

- `app/`
- `routes/`
- `database/`
- `resources/`
- `config/`
- `composer.json`
- `.env.example`

Root-level deploy/analysis artifacts (`Dockerfile`, `docker/`, `.dockerignore`,
`INVENTORY.md`, etc.) are **outside** the compared trees and are handled as VEARA_ONLY
(preserved). See the VEARA_ONLY section.

## Summary counts

| Status      | Count | Meaning                                                            |
|-------------|-------|--------------------------------------------------------------------|
| IDENTICAL   | 193   | Byte-identical in both; no action in any phase.                    |
| DIFFERENT   | 32    | Present in both but diverged. Phase 3 action (overwrite or merge). |
| NEW         | 35    | Only in Admin Dashboard. Phase 2 action (copy).                    |
| VEARA_ONLY  | 5     | Only in veara-backend (within/around scope). Preserved, untouched. |
| **Admin total (compared trees)** | **260** | (193 + 32 + 35) |

Models cross-check: Admin has 14 models, veara has 11. The 3 missing (`VearaProducts`,
`Garment`, `GarmentVariant`) are all in the NEW list. ✅ Consistent.

Migrations cross-check: Admin has 31 migrations, veara has 21. 10 are NEW, 4 are DIFFERENT,
17 are IDENTICAL. ✅ Consistent (21 shared in veara; +10 new = 31 in Admin).

---

## ⚠️ FLAGGED — careful-merge files (NOT plain overwrites in Phase 3)

These four files must **not** be blindly overwritten. Per user instruction they get a
careful, line-level merge that preserves veara-backend's intentional divergences.

### 1. `config/database.php` — DO NOT OVERWRITE (careful merge)

veara-backend intentionally diverged for the Cloud Run / Supabase (Postgres) deploy.

| Aspect | veara-backend (KEEP) | Admin Dashboard | Phase 3 action |
|--------|----------------------|-----------------|----------------|
| `default` connection | `env('DB_CONNECTION', 'pgsql')` (line 20) | `env('DB_CONNECTION', 'sqlite')` (line 19) | **KEEP veara (`pgsql`)** — intentional Cloud Run/Supabase default |
| `pgsql.schema` key | *absent* | `'schema' => env('DB_SCHEMA', 'public')` | **ADD Admin's key** to veara's pgsql block |

**Planned Phase 3 result:** veara keeps `'default' => env('DB_CONNECTION', 'pgsql')`, and
the single line `'schema' => env('DB_SCHEMA', 'public'),` is inserted into the `pgsql`
connection block (between `'prefix_indexes' => true,` and `'search_path' => 'public',`,
matching Admin's position). Nothing else changes.

Raw diff:
```diff
19,20c19
<     // 'default' => env('DB_CONNECTION', 'sqlite'),
<     'default' => env('DB_CONNECTION', 'pgsql'),     # veara: KEEP this
---
>     'default' => env('DB_CONNECTION', 'sqlite'),    # admin: DO NOT adopt
96a96
>             'schema' => env('DB_SCHEMA', 'public'),  # admin: ADD this to veara
```

### 2. `routes/api.php` — careful merge (REVIEW BEFORE PHASE 3)

veara-backend made an **intentional design decision**: public/anonymous product browsing.
It moved `get-all`, `get-one`, and `catalog-data` OUT of the `jwt.verify` group and INTO
the public group (veara `api.php` lines 29-33), with an explaining comment at line 68
("...moved to the public group above — VEARA supports anonymous browsing.").

Admin Dashboard, by contrast:
- Comments OUT `get-all`/`get-one` in the public group (admin lines 32-33), keeping only
  `catalog-data` public.
- Puts `get-all`/`get-one` INSIDE the `jwt.verify` group (admin lines 73-74) — i.e.
  authenticated-only browsing.

**These two are in direct semantic conflict.** Per user instruction, the plan is:

> Keep veara-backend's public-browsing structure; graft in Admin's genuinely-NEW routes only.

Routes that are **NEW in Admin** and will be ADDED to veara's `api.php`:
- Imports: `use App\Http\Controllers\API\V1\GarmentController;` and
  `use App\Http\Controllers\API\V2\ProductController as ProductControllerV2;`
- DesignController additions inside the jwt group:
  `GET /v1/designs/variants/{id}` → `designVariants`,
  `GET /v1/designs/{id}` → `getDesignDetails`,
  `DELETE /v1/designs/delete/{id}` → `deleteDesign`
- New `v2/products` group (no auth, admin lines 81-85):
  `GET /v2/products/get-all`, `GET /v2/products/get-one/{id}`,
  `GET /v2/products/show-just-designed-product/{id}`
- New public garment routes (admin lines 90-91):
  `GET /v1/garments`, `GET /v1/garments/{id}`

Routes that will **NOT** be added (would duplicate/conflict with veara's public versions):
- Admin's authenticated `GET /v1/products/get-all` and `GET /v1/products/get-one/{id}`
  (veara keeps these PUBLIC). `catalog-data` is already public in both.

**Reviewer note:** the user has asked to manually review the final `routes/api.php` before
Phase 3 writes it. Phase 3 will present the proposed merged file for review.

### 3. `routes/backend.php` — superset overwrite (safe)

Admin Dashboard's `backend.php` is a **strict superset** of veara's (verified: zero
veara-unique lines). Overwriting therefore equals merging. The Admin additions are:
- Imports: `VearaProductController`, `GarmentController`, `GarmentVariantController`
- Route group: VearaProduct CRUD (`/veara-product*`)
- Route group: Garment CRUD + `/garment/blueprint/{id}/print-providers`
- Route group: GarmentVariant CRUD + `/garment-variant/{id}/printify-variants`

Phase 3 action: overwrite with Admin's version (no veara content lost).

### 4. `composer.json` — careful merge (net effect: NO CHANGE)

The **only** difference is that **veara-backend HAS** `"doctrine/dbal": "^4.4"` (veara line 13)
which **Admin Dashboard lacks**. Admin has **no** packages that veara lacks.

```diff
13d12
<         "doctrine/dbal": "^4.4",     # veara-unique — KEEP
```

Per the merge rule ("keep all veara-unique entries + add all Admin entries"):
- Keep `doctrine/dbal: ^4.4` (veara-unique).
- Add Admin packages not in veara: **none.**

**Packages added: 0. Net Phase 3 change to `composer.json`: none** (file stays as-is).
`composer.lock` is NOT touched (user runs `composer install` after review).

---

## DIFFERENT files (32) — full list

Legend: `[merge]` = careful merge (see flags above); all others = plain overwrite with
Admin's version in Phase 3 (Admin is source of truth; known-broken items stay as-is).

```
app/Helpers/PrintifyGetAllProducts.php
app/Helpers/PrintifyGetOneProduct.php
app/Helpers/PrintifyService.php
app/Http/Controllers/API/V1/CartController.php
app/Http/Controllers/API/V1/DesignController.php
app/Http/Controllers/API/V1/PaymentController.php
app/Http/Controllers/API/V1/ProductController.php
app/Http/Controllers/API/V1/WebhookController.php
app/Http/Requests/Api/V1/Cart/CartProductInfoRequest.php
app/Http/Requests/Api/V1/Design/DesignSaveRequest.php
app/Http/Requests/Api/V1/Payment/CheckoutRequest.php
app/Http/Resources/Api/V1/Cart/CartDetailsResource.php
app/Http/Resources/Api/V1/Cart/CartItemGetResource.php
app/Http/Resources/Api/V1/Cart/MyOrdersResource.php
app/Http/Resources/Api/V1/Payment/CartDetailsResource.php
app/Models/Cart.php
app/Models/CartItem.php
app/Models/ContactUs.php
app/Models/Design.php
app/Models/DesignElements.php
app/Models/DesignRender.php
app/Models/Order.php
app/Models/OrderItem.php
composer.json                                          [merge — net no change]
config/database.php                                    [merge — keep pgsql default, add schema key]
database/migrations/0001_01_01_000000_create_users_table.php
database/migrations/2026_03_08_083407_create_design_elements_table.php
database/migrations/2026_03_08_091241_create_carts_table.php
database/migrations/2026_03_15_024747_create_contact_us_table.php
resources/views/backend/partials/sidebar.blade.php    [superset overwrite — no veara content lost]
routes/api.php                                         [merge — REVIEW BEFORE PHASE 3]
routes/backend.php                                     [superset overwrite — no veara content lost]
```

Per-file divergence magnitude (veara-only lines / admin-only lines) — for the non-merge
files, Admin is consistently the richer/newer side:

```
app/Helpers/PrintifyGetAllProducts.php           v:1   a:1
app/Helpers/PrintifyGetOneProduct.php            v:1   a:1
app/Helpers/PrintifyService.php                  v:14  a:101
app/Http/Controllers/API/V1/CartController.php   v:29  a:71
app/Http/Controllers/API/V1/DesignController.php v:17  a:172
app/Http/Controllers/API/V1/PaymentController.php v:21 a:42
app/Http/Controllers/API/V1/ProductController.php v:3  a:3
app/Http/Controllers/API/V1/WebhookController.php v:8  a:10
app/Http/Requests/.../Cart/CartProductInfoRequest.php  v:14 a:14
app/Http/Requests/.../Design/DesignSaveRequest.php     v:5  a:7
app/Http/Requests/.../Payment/CheckoutRequest.php      v:16 a:15
app/Http/Resources/.../Cart/CartDetailsResource.php    v:1  a:3
app/Http/Resources/.../Cart/CartItemGetResource.php    v:1  a:4
app/Http/Resources/.../Cart/MyOrdersResource.php       v:1  a:4
app/Http/Resources/.../Payment/CartDetailsResource.php v:2  a:4
app/Models/Cart.php          v:1  a:4
app/Models/CartItem.php      v:2  a:41
app/Models/ContactUs.php     v:1  a:4
app/Models/Design.php        v:0  a:7
app/Models/DesignElements.php v:1 a:13
app/Models/DesignRender.php  v:1  a:4
app/Models/Order.php         v:1  a:6
app/Models/OrderItem.php     v:3  a:28
```

> **Note (informational):** `WebhookController.php` and other files may carry the known-broken
> items (Printify webhook commented out, `Session::retrieve()` facade, OTP hardcoded to 1111).
> Per instruction these are **not** fixed here — Phase 3 simply adopts Admin Dashboard's version
> as-is. A separate security pass handles them.

---

## DIFFERENT migrations — head-diffs

Format: `< veara-backend` / `> Admin Dashboard`. Admin Dashboard's version wins (overwrite).

### `0001_01_01_000000_create_users_table.php`
```diff
19c19
<             $table->string('last_name');
---
>             $table->string('last_name')->nullable();
```
Admin makes `last_name` nullable.

### `2026_03_08_083407_create_design_elements_table.php`
```diff
16a17
>             $table->jsonb('design_labels');
18a20
>             $table->string('placement'); // front_center / back_top / back_bottom
26,27c28
<             $table->string('status')->default('active');
<             // $table->enum('status', ['active', 'inactive']);
---
>             $table->enum('status', ['active', 'inactive']);
```
Admin adds `design_labels` (jsonb) and `placement`; switches `status` from string-default
to a real enum.

### `2026_03_08_091241_create_carts_table.php`
```diff
19,20c19
<             $table->string('status')->default('active');
<             // $table->enum('status', ['active', 'completed', 'abandoned']);
---
>             $table->enum('status', ['active', 'completed', 'abandoned']);
```
Admin switches `status` from string-default to a real enum.

### `2026_03_15_024747_create_contact_us_table.php`
```diff
23,27d22
<
<         Schema::table('users', function (Blueprint $table) {
<             //
<             // $table->string('phone')->after('avatar');
<         });
36,40d30
<
<         Schema::table('users', function (Blueprint $table) {
<             //
<             $table->dropColumn('phone');
<         });
```
**This migration LOSES veara-unique lines on overwrite (v:10, a:0).** veara has stray
`Schema::table('users', ...)` blocks — a commented-out `phone` add in `up()` and an active
`dropColumn('phone')` in `down()`. The `down()` drop is arguably buggy (drops a column that
`up()` never adds). Admin removed these blocks (cleaner). Overwrite adopts Admin's cleaner
version. Noted for reviewer awareness.

---

## NEW files (35) — copied in Phase 2

**Controllers (admin backend):**
```
app/Http/Controllers/Web/Backend/VearaProductController.php
app/Http/Controllers/Web/Backend/GarmentController.php
app/Http/Controllers/Web/Backend/GarmentVariantController.php
```
**Controllers (customer API):**
```
app/Http/Controllers/API/V1/GarmentController.php
app/Http/Controllers/API/V2/ProductController.php
```
**Requests:**
```
app/Http/Requests/Api/V1/Garment/GarmentIndexRequest.php
```
**Resources:**
```
app/Http/Resources/Api/V1/Cart/OrderItemsResource.php
app/Http/Resources/Api/V1/Garment/GarmentDetailsGetResource.php
app/Http/Resources/Api/V1/Garment/GarmentGetResource.php
app/Http/Resources/Api/V1/Garment/GarmentVariantResource.php
app/Http/Resources/Api/V2/Products/JustDesignedGetResource.php
app/Http/Resources/Api/V2/Products/ProductDetailsGetResource.php
app/Http/Resources/Api/V2/Products/ProductGetResource.php
```
**Models:**
```
app/Models/VearaProducts.php
app/Models/Garment.php
app/Models/GarmentVariant.php
```
**Migrations (10):**
```
database/migrations/2026_05_04_100726_create_veara_products_table.php
database/migrations/2026_05_09_053632_create_garments_table.php
database/migrations/2026_05_09_053650_create_garment_variants_table.php
database/migrations/2026_05_19_070501_add_title_price_description_to_veara_products_table.php
database/migrations/2026_05_20_060558_add_print_area_specs_to_garments_table.php
database/migrations/2026_05_20_060609_modify_cart_items_table.php
database/migrations/2026_05_20_060615_modify_order_items_table.php
database/migrations/2026_05_20_101046_add_category_to_veara_products_table.php
database/migrations/2026_05_21_131314_modify_design_elements_table_add_scale_angle.php
database/migrations/2026_05_21_135512_modify_order_and_design_tables_for_checkout.php
```
**Views (Blade):**
```
resources/views/backend/layout/Garment/index.blade.php
resources/views/backend/layout/GarmentVariant/index.blade.php
resources/views/backend/layout/VearaProduct/index.blade.php
resources/views/backend/partials/garment/add.blade.php
resources/views/backend/partials/garment/index_main.blade.php
resources/views/backend/partials/garment_variant/add.blade.php
resources/views/backend/partials/garment_variant/index_main.blade.php
resources/views/backend/partials/veara_product/add.blade.php
resources/views/backend/partials/veara_product/index_main.blade.php
```

---

## VEARA_ONLY files (preserved — never touched)

These exist only in veara-backend (deploy + analysis artifacts) and are explicitly preserved:

```
Dockerfile              (Cloud Run deploy artifact)
.dockerignore
docker/nginx.conf
docker/supervisord.conf
INVENTORY.md            (prior analysis document)
```

Notes:
- `cloudbuild.yaml` — does NOT exist in veara-backend (nothing to preserve).
- `.env.example` — IDENTICAL in both, so no augmentation needed (no Admin-only env keys).
- `MERGE_DIFF.md` / `MERGE_REPORT.md` — created by this merge process itself.

---

## Environment / repo observations

- **Admin Dashboard `.git`:** The pre-condition said Admin Dashboard has "no `.git`", but it
  **does** have one: a single clean commit `72ff873 "Initial commit before reorganization"`,
  no remote, clean working tree. This is **harmless** for the merge — `.git` is outside the
  compared trees and is never copied. Flagged for transparency only.
- Admin Dashboard also has a root `FILE_CLASSIFICATION.md` (analysis doc) outside compared
  trees; not copied.

---

## IDENTICAL files (193)

Byte-identical in both projects; no action in any phase. Full list in the appendix below.
Spot-checked key items confirmed IDENTICAL: `app/Enum/Role.php`,
`app/Mail/Api/V1/SendOtpMail.php`, `app/Http/Middleware/AdminMiddleware.php`,
`app/Http/Middleware/JWTMiddleware.php`, `config/permission.php`.

### Appendix: IDENTICAL file list
```
.env.example
app/Enum/Role.php
app/Helpers/helpers.php
app/Http/Controllers/API/Auth/User/AuthenticationController.php
app/Http/Controllers/API/Auth/User/ProfileController.php
app/Http/Controllers/API/Auth/User/SocialAuthController.php
app/Http/Controllers/API/V1/ContactUsController.php
app/Http/Controllers/Auth/AuthenticatedSessionController.php
app/Http/Controllers/Auth/ConfirmablePasswordController.php
app/Http/Controllers/Auth/EmailVerificationNotificationController.php
app/Http/Controllers/Auth/EmailVerificationPromptController.php
app/Http/Controllers/Auth/NewPasswordController.php
app/Http/Controllers/Auth/PasswordController.php
app/Http/Controllers/Auth/PasswordResetLinkController.php
app/Http/Controllers/Auth/RegisteredUserController.php
app/Http/Controllers/Auth/VerifyEmailController.php
app/Http/Controllers/Controller.php
app/Http/Controllers/ProfileController.php
app/Http/Controllers/Web/Backend/ContactUsController.php
app/Http/Controllers/Web/Backend/DashboardController.php
app/Http/Controllers/Web/Backend/DynamicPageController.php
app/Http/Controllers/Web/Backend/OrdersController.php
app/Http/Controllers/Web/Backend/PermissionController.php
app/Http/Controllers/Web/Backend/ProfileController.php
app/Http/Controllers/Web/Backend/RoleController.php
app/Http/Controllers/Web/Backend/SettingController.php
app/Http/Controllers/Web/Backend/UserController.php
app/Http/Controllers/Web/Backend/UserRoleManagementController.php
app/Http/Middleware/AdminMiddleware.php
app/Http/Middleware/JWTMiddleware.php
app/Http/Requests/Api/V1/Auth/ForgotPasswordEmailRequest.php
app/Http/Requests/Api/V1/Auth/ForgotPasswordVerifyOtpRequest.php
app/Http/Requests/Api/V1/Auth/LoginRequest.php
app/Http/Requests/Api/V1/Auth/OtpVerifyRequest.php
app/Http/Requests/Api/V1/Auth/ResetPasswordRequest.php
app/Http/Requests/Api/V1/Auth/SignupRequest.php
app/Http/Requests/Api/V1/Auth/SocialAuthRequest.php
app/Http/Requests/Api/V1/Cart/UpdateQuantityRequest.php
app/Http/Requests/Api/V1/ContactUs/ContactUsRequest.php
app/Http/Requests/Api/V1/Profile/ChangePasswordRequest.php
app/Http/Requests/Api/V1/Profile/UpdateRequest.php
app/Http/Requests/Auth/LoginRequest.php
app/Http/Requests/ProfileUpdateRequest.php
app/Http/Resources/Api/V1/Auth/SocialAuthResource.php
app/Http/Resources/Api/V1/Auth/UserResource.php
app/Http/Resources/Api/V1/Cart/CartGetResource.php
app/Http/Resources/Api/V1/ContactUs/ContactUsResource.php
app/Http/Resources/Api/V1/Design/DesignElementsGetResource.php
app/Http/Resources/Api/V1/Design/DesignGetResource.php
app/Http/Resources/Api/V1/Design/DesignRenderGetResource.php
app/Http/Resources/Api/V1/DesignPageGetResource.php
app/Http/Resources/Api/V1/Products/JustDesignedGetResource.php
app/Http/Resources/Api/V1/Products/ProductDetailsGetResource.php
app/Http/Resources/Api/V1/Products/ProductGetResource.php
app/Http/Resources/Api/V1/Profile/ChangePasswordResource.php
app/Http/Resources/Api/V1/Profile/getResource.php
app/Http/Resources/Api/V1/Profile/UpdateResource.php
app/Mail/Api/V1/SendOtpMail.php
app/Mail/NotifyUser.php
app/Models/DynamicPage.php
app/Models/Setting.php
app/Models/User.php
app/Providers/AppServiceProvider.php
app/Traits/ApiResponse.php
app/View/Components/AppLayout.php
app/View/Components/Backend/Setting/ImageUpload.php
app/View/Components/GuestLayout.php
app/View/Components/ImageUpload.php
config/app.php
config/auth.php
config/cache.php
config/datatables.php
config/filesystems.php
config/jwt.php
config/logging.php
config/mail.php
config/permission.php
config/queue.php
config/sanctum.php
config/services.php
config/session.php
database/.gitignore
database/factories/UserFactory.php
database/migrations/0001_01_01_000001_create_cache_table.php
database/migrations/0001_01_01_000002_create_jobs_table.php
database/migrations/2025_06_27_052222_create_personal_access_tokens_table.php
database/migrations/2025_06_28_130024_create_settings_table.php
database/migrations/2025_07_18_122445_create_permission_tables.php
database/migrations/2025_08_27_084502_create_dynamic_pages_table.php
database/migrations/2026_03_08_081816_create_designs_table.php
database/migrations/2026_03_08_084503_create_design_renders_table.php
database/migrations/2026_03_08_092002_create_cart_items_table.php
database/migrations/2026_03_15_035349_create_orders_table.php
database/migrations/2026_03_16_040626_create_order_items_table.php
database/migrations/2026_03_17_024248_add_mockup_column.php
database/migrations/2026_03_17_031646_add_and_remove_column_from_orders.php
database/migrations/2026_03_23_041914_add_stripe_session_id.php
database/migrations/2026_03_23_055239_cart_items_columns_nullable.php
database/migrations/2026_03_24_055239_order_items_columns_nullable.php
database/migrations/2026_03_26_100417_add_order_image.php
database/seeders/CartSeeder.php
database/seeders/DatabaseSeeder.php
database/seeders/PermissionSeeder.php
database/seeders/UserSeeder.php
resources/css/app.css
resources/js/app.js
resources/js/bootstrap.js
resources/views/auth/app.blade.php
resources/views/auth/confirm-password.blade.php
resources/views/auth/forgot-password.blade.php
resources/views/auth/login.blade.php
resources/views/auth/partials/script.blade.php
resources/views/auth/partials/style.blade.php
resources/views/auth/register.blade.php
resources/views/auth/reset-password.blade.php
resources/views/auth/verify-email.blade.php
resources/views/backend/app.blade.php
resources/views/backend/dashboard.blade.php
resources/views/backend/layout/ContactUs/index.blade.php
resources/views/backend/layout/dynamic_pages/create.blade.php
resources/views/backend/layout/dynamic_pages/edit.blade.php
resources/views/backend/layout/dynamic_pages/index.blade.php
resources/views/backend/layout/Order_details/index.blade.php
resources/views/backend/layout/Orders/index.blade.php
resources/views/backend/layout/Permission/index.blade.php
resources/views/backend/layout/Profile/index.blade.php
resources/views/backend/layout/Role/edit.blade.php
resources/views/backend/layout/Role/index.blade.php
resources/views/backend/layout/Setting/index.blade.php
resources/views/backend/layout/Setting/printify_index.blade.php
resources/views/backend/layout/Setting/smtp_index.blade.php
resources/views/backend/layout/Setting/stripe_index.blade.php
resources/views/backend/layout/stripe/cancel.blade.php
resources/views/backend/layout/stripe/success.blade.php
resources/views/backend/layout/User/index.blade.php
resources/views/backend/partials/brand_logo.blade.php
resources/views/backend/partials/contact_us/add.blade.php
resources/views/backend/partials/contact_us/index_main.blade.php
resources/views/backend/partials/dynamic_pages/create_main.blade.php
resources/views/backend/partials/dynamic_pages/edit_main.blade.php
resources/views/backend/partials/dynamic_pages/index_main.blade.php
resources/views/backend/partials/footer.blade.php
resources/views/backend/partials/header.blade.php
resources/views/backend/partials/notification.blade.php
resources/views/backend/partials/order_details/index_main.blade.php
resources/views/backend/partials/orders/add.blade.php
resources/views/backend/partials/orders/index_main.blade.php
resources/views/backend/partials/Permisson/add_modal.blade.php
resources/views/backend/partials/Permisson/index_main.blade.php
resources/views/backend/partials/profile/email_edit.blade.php
resources/views/backend/partials/profile/information_change.blade.php
resources/views/backend/partials/profile/password_change.blade.php
resources/views/backend/partials/Role/add_role_modal.blade.php
resources/views/backend/partials/Role/index_main.blade.php
resources/views/backend/partials/script.blade.php
resources/views/backend/partials/scroll.blade.php
resources/views/backend/partials/search.blade.php
resources/views/backend/partials/setting/printify_content.blade.php
resources/views/backend/partials/setting/smtp_content.blade.php
resources/views/backend/partials/setting/stripe_content.blade.php
resources/views/backend/partials/style.blade.php
resources/views/backend/partials/theme_mode.blade.php
resources/views/backend/partials/user_profile.blade.php
resources/views/backend/partials/user/add.blade.php
resources/views/backend/partials/user/index_main.blade.php
resources/views/components/application-logo.blade.php
resources/views/components/auth-session-status.blade.php
resources/views/components/backend/setting/image-upload.blade.php
resources/views/components/danger-button.blade.php
resources/views/components/dropdown-link.blade.php
resources/views/components/dropdown.blade.php
resources/views/components/input-error.blade.php
resources/views/components/input-label.blade.php
resources/views/components/modal.blade.php
resources/views/components/nav-link.blade.php
resources/views/components/primary-button.blade.php
resources/views/components/responsive-nav-link.blade.php
resources/views/components/secondary-button.blade.php
resources/views/components/text-input.blade.php
resources/views/dashboard.blade.php
resources/views/email/notify_user_mail.blade.php
resources/views/email/otp_mail.blade.php
resources/views/layouts/app.blade.php
resources/views/layouts/guest.blade.php
resources/views/layouts/navigation.blade.php
resources/views/profile/edit.blade.php
resources/views/profile/partials/delete-user-form.blade.php
resources/views/profile/partials/update-password-form.blade.php
resources/views/profile/partials/update-profile-information-form.blade.php
resources/views/welcome.blade.php
routes/auth.php
routes/console.php
routes/web.php
```
