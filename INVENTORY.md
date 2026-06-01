# veara-backend — Current State Inventory

> Read-only inventory produced by static analysis of the `veara-backend` Laravel project.
> No code was modified. Every claim cites `file:line`.
> Scope analyzed: `app/`, `routes/`, `database/migrations/`, `database/seeders/`, `bootstrap/`, project configs in `config/`; Blade files under `resources/views/` are listed but not deep-analyzed. Excluded: `vendor/`, `node_modules/`, `storage/`, `public/`, `tests/`.

---

## 1. Project meta

- **Laravel version:** `laravel/framework: ^11.0` (`composer.json:14`). The skeleton is the stock `laravel/laravel` project (`composer.json:2`).
- **PHP requirement:** `php: ^8.2` (`composer.json:12`). Runtime image is PHP 8.3 (`Dockerfile:1`).
- **Key packages installed** (`composer.json:11-26`):
  - `laravel/sanctum: ^4.0` (`composer.json:15`) — installed; **no Sanctum guard or middleware is actually wired** (the API uses JWT instead). `personal_access_tokens` table migration exists (`database/migrations/2025_06_27_052222_create_personal_access_tokens_table.php`).
  - `php-open-source-saver/jwt-auth: ^2.8` (`composer.json:18`) — **this is the active API auth mechanism** (`config/auth.php:43-46` sets `api` guard driver to `jwt`).
  - `laravel/socialite: ^5.21` + `socialiteproviders/apple: ^5.7` (`composer.json:16,19`) — Google + Apple social login.
  - `spatie/laravel-permission: 6.17` (`composer.json:21`) — roles/permissions (pinned exact version).
  - `stripe/stripe-php: ^19.4` (`composer.json:22`) — Stripe Checkout + webhook.
  - `yajra/laravel-datatables: 11.0` + `yajra/laravel-datatables-oracle: ^11.1` (`composer.json:23-24`) — admin DataTables.
  - `league/flysystem-aws-s3-v3: 3.0` (`composer.json:17`) — S3 filesystem support.
  - `doctrine/dbal: ^4.4` (`composer.json:13`) — required for `->change()` column migrations.
  - **No Printify SDK** — Printify is called via raw `Http` client helpers (`app/Helpers/Printify*.php`).
  - Dev: `laravel/breeze: ^2.3` (`composer.json:31`) — Breeze auth scaffolding is present (Blade auth views + `app/Http/Controllers/Auth/*`).
  - `app/Helpers/helpers.php` is autoloaded as a file (`composer.json:42-44`).
- **Database driver in `.env.example`:** `DB_CONNECTION=sqlite` (`.env.example:22`). All other DB_* lines are commented out.
- **Cloud / hosting indicators:**
  - `Dockerfile` present (PHP 8.3-fpm-alpine + nginx + supervisor; `Dockerfile:1-42`). Exposes port 8080 (`Dockerfile:40`) — consistent with Cloud Run.
  - `docker/` directory with `nginx.conf` and `supervisord.conf` (`Dockerfile:34-35`).
  - `.dockerignore` present.
  - Health check route `/up` registered (`bootstrap/app.php:14`).
  - **Driver drift:** the Dockerfile installs `pdo_pgsql pgsql` (`Dockerfile:15`) but also `touch`es `database/database.sqlite` (`Dockerfile:28`); `.env.example` says sqlite (`.env.example:22`). The committed migrations include MySQL/sqlite-style stuff; intended production driver is ambiguous (see §11).
  - No `cloudbuild.yaml`, `app.yaml`, or `Procfile` found at project root.
- **Would `php artisan route:list` likely succeed?** **Likely yes**, based on syntax review. All controllers referenced in route files exist (see §3/§4), middleware aliases `admin` and `jwt.verify` are registered (`bootstrap/app.php:22-25`), and `routes/backend.php` is mounted via the `then:` closure (`bootstrap/app.php:16-19`). Caveats: `routes/backend.php` uses the `Route::` facade with no `use` import and no `<?php` route file guard, which is the normal Laravel pattern and resolves at runtime via the global alias. No syntax errors were observed in any route file.

---

## 2. Directory tree (relevant roots)

```
app/
├── Enum/
│   └── Role.php
├── Helpers/
│   ├── helpers.php
│   ├── PrintifyGetAllProducts.php
│   ├── PrintifyGetOneProduct.php
│   └── PrintifyService.php
├── Http/
│   ├── Controllers/
│   │   ├── API/Auth/User/{AuthenticationController,ProfileController,SocialAuthController}.php
│   │   ├── API/V1/{Cart,ContactUs,Design,Payment,Product,Webhook}Controller.php
│   │   ├── Auth/  (9 Breeze controllers)
│   │   ├── Web/Backend/  (11 admin controllers)
│   │   ├── Controller.php
│   │   └── ProfileController.php   (legacy Breeze)
│   ├── Middleware/{AdminMiddleware,JWTMiddleware}.php
│   ├── Requests/  (Api/V1/* form requests + Auth/LoginRequest + ProfileUpdateRequest)
│   └── Resources/Api/V1/  (~17 API resources)
├── Mail/{NotifyUser.php, Api/V1/SendOtpMail.php}
├── Models/{User,Cart,CartItem,Design,DesignElements,DesignRender,DynamicPage,Order,OrderItem,ContactUs,Setting}.php
├── Providers/AppServiceProvider.php
├── Traits/ApiResponse.php
└── View/Components/{AppLayout,GuestLayout,ImageUpload, Backend/Setting/ImageUpload}.php

routes/
├── api.php
├── auth.php
├── backend.php
├── console.php
└── web.php

database/
├── factories/UserFactory.php
├── migrations/  (20 files, see §6)
└── seeders/{DatabaseSeeder,UserSeeder,PermissionSeeder,CartSeeder}.php

bootstrap/
├── app.php
├── providers.php
└── cache/{packages.php, services.php}

config/
├── app.php, auth.php, cache.php, database.php, datatables.php, filesystems.php,
├── jwt.php, logging.php, mail.php, permission.php, queue.php, sanctum.php,
├── services.php, session.php
```

---

## 3. Route inventory

### `routes/api.php` (prefix `/api`)

**Public (no auth):**
| Method | Path | Handler | Middleware |
|---|---|---|---|
| POST | `v1/sign-up` | `AuthenticationController@signup` | — | (`api.php:16-17`) |
| POST | `v1/verify/otp` | `AuthenticationController@verifyOtp` | — | (`api.php:18`) |
| POST | `v1/login` | `AuthenticationController@login` | — | (`api.php:19`) |
| POST | `v1/forgot-password` | `AuthenticationController@forgotPasswordEmail` | — | (`api.php:20`) |
| POST | `v1/forgot-password/verifyOtp` | `AuthenticationController@forgotPasswordVerifyOtp` | — | (`api.php:21`) |
| POST | `v1/reset-password` | `AuthenticationController@resetPassword` | — | (`api.php:22`) |
| POST | `v1/social-login` | `SocialAuthController@SocialLogin` | — | (`api.php:27`) |
| GET | `v1/products/get-all` | `ProductController@index` | — | (`api.php:30`) |
| GET | `v1/products/get-one/{id}` | `ProductController@productDetail` | — | (`api.php:31`) |
| GET | `v1/products/catalog-data` | `ProductController@productTags` | — | (`api.php:32`) |
| POST | `v1/contact-us` | `ContactUsController@store` | — | (`api.php:35`) |

**Authenticated (`jwt.verify` group, `api.php:38`):**
| Method | Path | Handler |
|---|---|---|
| GET | `v1/auth/profile` | `API\Auth\User\ProfileController@index` (`api.php:43`) |
| PUT | `v1/auth/profile` | `…ProfileController@update` (`api.php:44`) |
| POST | `v1/auth/logout` | `…ProfileController@logout` (`api.php:45`) |
| POST | `v1/auth/change-password` | `…ProfileController@changePassword` (`api.php:46`) |
| POST | `v1/products/add-to-cart` | `CartController@addToCart` (`api.php:50`) |
| GET | `v1/products/my-orders` | `CartController@myOrders` (`api.php:51`) |
| GET | `v1/products/cart-details` | `CartController@cartDetails` (`api.php:52`) |
| DELETE | `v1/products/remove-from-cart/{id}` | `CartController@removeFromCart` (`api.php:53`) |
| POST | `v1/products/update-quantity` | `CartController@updateQuantity` (`api.php:54`) |
| GET | `v1/payment/checkout/{id}` | `PaymentController@index` (`api.php:58`) |
| POST | `v1/payment/checkout/initiate` | `PaymentController@checkout` (`api.php:59`) |
| GET | `v1/designs/` | `DesignController@index` (`api.php:63`) |
| POST | `v1/designs/save` | `DesignController@saveDesign` (`api.php:64`) |
| GET | `v1/products/show-just-designed-product/{id}` | `ProductController@justDesignedProduct` (`api.php:69`) |

**Public payment/webhook (outside auth group):**
| Method | Path | Handler | Note |
|---|---|---|---|
| GET | `/api/payment/success` | `PaymentController@success` (named `payment.success`) | `api.php:74` |
| GET | `/api/payment/cancel` | `PaymentController@cancel` (named `payment.cancel`) | `api.php:75` |
| POST | `v1/webhooks/stripe` | `WebhookController@stripe` | signature-verified, no middleware (`api.php:79`) |
| POST | `v1/webhooks/printify` | `WebhookController@printify` | no signature verification (`api.php:80`) |

### `routes/web.php`
- `GET /` → `redirect('admin/dashboard')` (`web.php:7-9`).
- Two commented-out blocks for `/dashboard` and `/profile` (`web.php:11-19`).
- `require __DIR__.'/auth.php'` (`web.php:21`).
- Imports `PaymentController` and legacy `ProfileController` but **does not use them** (`web.php:3-4`).

### `routes/auth.php` (Breeze, loaded via web.php)
- **Register routes are commented out** (`auth.php:15-18`) — no public web registration.
- `guest` group: `GET/POST login` (`auth.php:20-23`), `GET/POST forgot-password` (`auth.php:25-29`), `GET reset-password/{token}` + `POST reset-password` (`auth.php:31-35`).
- `auth` group: email verification (`auth.php:39-48`), confirm-password (`auth.php:50-53`), `PUT password` (`auth.php:55`), `POST logout` (`auth.php:57-58`).

### `routes/backend.php` (prefix `/admin`, middleware `web, admin, auth` — `bootstrap/app.php:16-18`)
| Method | Path | Handler | Name |
|---|---|---|---|
| GET | `/admin/dashboard` | `DashboardController@index` | `dashboard` (`backend.php:18`) |
| GET | `/admin/profile` | `Web\Backend\ProfileController@index` | `profile.edit` (`backend.php:21`) |
| PATCH | `/admin/profile` | `…@update` | `profile.update` (`backend.php:22`) |
| PATCH | `/admin/password` | `…@passwordChange` | `password.change` (`backend.php:23`) |
| PATCH | `/admin/email-update` | `…@updateEmail` | `email.change` (`backend.php:24`) |
| GET/POST | `/admin/setting` | `SettingController@index/@store` | `setting.index/.store` (`backend.php:29-30`) |
| GET/POST | `/admin/smtp-setting` | `SettingController@smtpIndex/@smtpStore` | `smtp.index/.store` (`backend.php:33-34`) |
| GET/POST | `/admin/stripe-setting` | `SettingController@stripeKeysIndex/@stripeKeysStore` | `stripe.index/.store` (`backend.php:36-37`) |
| GET/POST | `/admin/printify-setting` | `SettingController@printifyKeysIndex/@printifyKeysStore` | `printify.index/.store` (`backend.php:39-40`) |
| GET/POST | `/admin/user` | `UserController@index/@store` | `user.index/.store` (`backend.php:46-47`) |
| GET | `/admin/user/{id}/show` | `UserController@show` | `user.show` (`backend.php:48`) — **see §11: no `show()` method exists** |
| GET | `/admin/user/{id}/edit` | `UserController@edit` | `user.edit` (`backend.php:49`) |
| PUT | `/admin/user/{id}/update` | `UserController@update` | `user.update` (`backend.php:50`) |
| DELETE | `/admin/user/{id}/delete` | `UserController@delete` | `user.delete` (`backend.php:51`) |
| GET | `/admin/roles` | `RoleController@index` | `roles.index` (`backend.php:57`) |
| GET | `/admin/roles/add` | `RoleController@create` | `roles.add` (`backend.php:58`) — **see §11: no `create()` method exists** |
| POST | `/admin/role/store` | `RoleController@store` | `role.store` (`backend.php:59`) |
| GET | `/admin/role/edit/{id}` | `RoleController@edit` | `role.edit` (`backend.php:60`) |
| POST | `/admin/role/update/{id}` | `RoleController@update` | `role.update` (`backend.php:61`) |
| DELETE | `/admin/role/delete/{id}` | `RoleController@destroy` | `role.delete` (`backend.php:62`) |
| GET | `/admin/permissions` | `PermissionController@index` | `permissions.index` (`backend.php:66`) |
| GET | `/admin/user/roles` | `UserRoleManagementController@index` | `user.roles.index` (`backend.php:71`) |
| POST | `/admin/user/{id}/attach-role` | `UserRoleManagementController@attachRole` | `user.attach.role` (`backend.php:72`) |
| POST | `/admin/user/{id}/detach-role` | `UserRoleManagementController@detachRole` | `user.detach.role` (`backend.php:73`) |
| GET | `/admin/dynamic-pages/` | `DynamicPageController@index` | `dynamic-pages.index` (`backend.php:79`) |
| GET | `/admin/dynamic-pages/create` | `DynamicPageController@create` | `dynamic-pages.create` (`backend.php:80`) |
| POST | `/admin/dynamic-pages/` | `DynamicPageController@store` | `dynamic-pages.store` (`backend.php:81`) |
| GET | `/admin/dynamic-pages/{id}/edit` | `DynamicPageController@edit` | `dynamic-pages.edit` (`backend.php:82`) |
| POST | `/admin/dynamic-pages/{id}` | `DynamicPageController@update` | `dynamic-pages.update` (`backend.php:83`) |
| DELETE | `/admin/dynamic-pages/{id}` | `DynamicPageController@destroy` | `dynamic-pages.delete` (`backend.php:84`) |
| GET | `/admin/orders/` | `OrdersController@index` | `orders.index` (`backend.php:88`) |
| GET | `/admin/orders/{id}` | `OrdersController@details` | `orders.details` (`backend.php:89`) |
| GET | `/admin/orders/{id}/data` | `OrdersController@detailsData` | `orders.details.data` (`backend.php:90`) |
| GET | `/admin/contact-us/` | `ContactUsController@index` | `contact_us.index` (`backend.php:94`) |

- `routes/backend.php` **does exist** (contradicts the assumption in the brief that a missing `backend.php` means no admin UI). The admin UI is present in this backend.

### `routes/console.php`
- Single `inspire` Artisan command (`console.php:6-7`). No scheduled tasks.

### Missing route files
- No dedicated `routes/channels.php` registered. No v2 API route file. No standalone admin-product/garment route file.

---

## 4. Controller inventory

### API — customer JWT API (`app/Http/Controllers/API/`)

- **`API/Auth/User/AuthenticationController.php`** — methods: `signup`, `verifyOtp`, `login`, `forgotPasswordEmail`, `forgotPasswordVerifyOtp`, `resetPassword`. Purpose: customer registration / login / password reset via JWT. **Returns JSON** (via `ApiResponse` trait). Notable: OTP email sending is fully commented out (`:33-48`, `:138`); `signup` creates the user directly with no OTP step (`:50-62`); `forgotPasswordEmail` hardcodes the OTP to `1111` (`:135`) and returns it in the response message (`:143`).
- **`API/Auth/User/ProfileController.php`** — methods: `index`, `update`, `changePassword`, `logout`. Purpose: authenticated customer profile read/update, avatar upload, password change, JWT logout/invalidate. **JSON.**
- **`API/Auth/User/SocialAuthController.php`** — method: `SocialLogin`. Purpose: Google/Apple stateless social login via `userFromToken` (`:27`). **JSON.** Notable: `$isNewUser`/`$token`/`$user` are only defined inside `if ($socialiteUser)` (`:29-55`) and referenced unconditionally afterward (`:57`) — undefined-variable risk if the token resolves falsy.
- **`API/V1/ProductController.php`** — methods: `index`, `productDetail`, `justDesignedProduct`, `productTags`. Purpose: proxy Printify live products (list with pagination + tag filter, single product, catalog tag list). **JSON.** No local product catalog — products are fetched live from Printify (`:19-29,51-54`). `productTags` returns a hardcoded tag map (`:77-83`).
- **`API/V1/CartController.php`** — methods: `addToCart`, `myOrders`, `cartDetails`, `removeFromCart`, `updateQuantity`. Purpose: cart CRUD keyed to an `active` cart per user, with soft-delete on items. **JSON.**
- **`API/V1/DesignController.php`** — methods: `index`, `saveDesign`. Purpose: list all designs with renders; persist a design + per-area `DesignRender` rows. **JSON.** Notable: `index` lists **all** designs, not filtered to the auth user (`:25`; `$user_id` is computed at `:23` then unused). `DesignElements` is never written here.
- **`API/V1/PaymentController.php`** — methods: `index`, `checkout`, `success`, `cancel`. Purpose: checkout summary (`index`), create Order + OrderItems + Stripe Checkout session (`checkout`), Stripe success/cancel landing pages. **Mixed:** `index`/`checkout` return JSON; `success`/`cancel` **return Blade views** (`backend.layout.stripe.success`/`cancel`, `:152,162,172`). Notable bug — see §11: `success()` calls `Session::retrieve()` but imports Laravel's `Illuminate\Support\Facades\Session` (`:17`), not Stripe's session class.
- **`API/V1/ContactUsController.php`** — method: `store`. Purpose: persist a contact-us submission. **JSON.**
- **`API/V1/WebhookController.php`** — methods: `stripe`, `printify`. Purpose: Stripe `checkout.session.completed` → mark cart completed + order paid; Printify `order:status:changed` → update order status. **JSON.** Notable: Printify order creation is **commented out** (`:50,63`); the `PrintifyService` constructor param is commented out of the signature (`:18`); the printify webhook keys on `printify_order_id` (`:89`) which is never set, and calls `->update()` on a possibly-null `$order` (`:89-90`).

### Web — admin UI (`app/Http/Controllers/Web/Backend/`)

- **`DashboardController.php`** — `index`. Returns Blade `backend.dashboard` (`:15`). **No stats/data passed** — static view only.
- **`OrdersController.php`** — `index` (DataTables JSON + Blade list), `details` (Blade), `detailsData` (DataTables JSON). **Both** (`:14-61`).
- **`ContactUsController.php`** — `index` (DataTables JSON + Blade). **Both** (`:13-22`).
- **`DynamicPageController.php`** — `index`, `create`, `store`, `edit`, `update`, `destroy`. Full CRUD for CMS pages. **Both** (Blade for index/create/edit; JSON for store/update/destroy) (`:16-161`).
- **`ProfileController.php`** (admin) — `index`, `update`, `updateEmail`, `passwordChange`. Admin self-profile management. **Blade + redirects** (`:17-127`).
- **`UserController.php`** — `index` (DataTables + Blade), `store`, `edit` (JSON), `update` (JSON), `delete` (JSON soft-delete). Customer/staff user management; sends welcome mail on create (`:127`). **Both.** **No `show()` method** despite the route (`backend.php:48`).
- **`RoleController.php`** — `index` (DataTables + Blade), `store`, `edit` (JSON), `update`, `destroy`. Spatie role CRUD with permission assignment. **Both.** **No `create()` method** despite the route (`backend.php:58`).
- **`PermissionController.php`** — `index`. Returns all permissions as JSON (`:14-31`). **JSON.**
- **`UserRoleManagementController.php`** — `index` (DataTables + Blade), `attachRole`, `detachRole`. Assign/remove Spatie roles to users. **Both.**
- **`SettingController.php`** — `index`, `store` (system title/logo/favicon), `smtpIndex`/`smtpStore`, `stripeKeysIndex`/`stripeKeysStore`, `printifyKeysIndex`/`printifyKeysStore`, plus protected `updateEnv`. Purpose: site settings + **writes SMTP/Stripe/Printify credentials directly into the `.env` file at runtime** (`:88-111,149-171,194-215`). **Blade + redirects.**

### Auth — Breeze scaffolding (`app/Http/Controllers/Auth/`, 9 files)
Stock Laravel Breeze controllers: `RegisteredUserController`, `AuthenticatedSessionController`, `ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `NewPasswordController`, `PasswordController`, `PasswordResetLinkController`, `VerifyEmailController`. Drive the `auth/*` Blade views. **Blade** (admin login uses `AuthenticatedSessionController`). Not deep-analyzed (unmodified Breeze).

### Other
- **`app/Http/Controllers/Controller.php`** — abstract base controller (`:`stock).
- **`app/Http/Controllers/ProfileController.php`** — **legacy Breeze profile controller** (`edit`/`update`/`destroy`, returns `profile.edit` Blade). Only referenced by commented-out routes in `web.php:16-18`; effectively dead.

---

## 5. Model inventory (`app/Models/`)

- **`User.php`** — table `users` (inferred). Fillable: `first_name, last_name, avatar, phone, email, password, role, address, bio, provider, provider_id` (`:24-36`). Hidden: `password, remember_token` (`:43-46`). Casts: `email_verified_at→datetime`, `password→hashed`, `role→Role` enum (`:53-60`). Traits: `HasFactory, Notifiable, SoftDeletes, HasRoles` (Spatie) (`:17`). Implements `JWTSubject` (`:14`, methods `:75-83`). `booted()` auto-assigns an incrementing `uuid` starting at 100001 (`:62-71`).
- **`Cart.php`** — table `carts`. `$guarded = []` (`:10`). Relations: `user()` belongsTo User (`:12`), `cart_items()` hasMany CartItem on `cart_id` (`:17`).
- **`CartItem.php`** — table `cart_items`. `SoftDeletes` (`:10`). `$guarded = []`. Relations: `cart()` belongsTo Cart, `design()` belongsTo Design (`:14-22`).
- **`Design.php`** — table `designs`. `$guarded = []`. Casts: `print_files→array` (`:12-14`). Relations: `user()` belongsTo User, `designImages()` hasMany DesignRender on `design_id` (`:16-24`).
- **`DesignElements.php`** — table `design_elements`. `$guarded = []`. Relation: `design_render()` belongsTo DesignRender on `design_render_id` (`:12`). **Model exists but is never instantiated by any controller.**
- **`DesignRender.php`** — table `design_renders`. `$guarded = []`. Relation: `design()` belongsTo Design (`:12`).
- **`Order.php`** — table `orders`. `$guarded = []`. Relations: `user()` belongsTo User, `order_item()` hasMany OrderItem on `order_id` (`:12-20`).
- **`OrderItem.php`** — table `order_items`. `$guarded = []`. Relations: `order()` belongsTo Order; `design()` **hasOne** Design (`:17-19`) — likely should be belongsTo (no `order_item_id` exists on designs).
- **`ContactUs.php`** — table `contact_us` (inferred; matches migration). `$guarded = []` (`:10`).
- **`DynamicPage.php`** — table `dynamic_pages`. Fillable: `title, slug, content` (`:9-13`).
- **`Setting.php`** — table `settings`. Fillable: `system_title, logo, favicon, copyright_text` (`:9-14`).

**No `VearaProduct`, `Garment`, or `GarmentVariant` model exists.** Product data is sourced live from Printify, not stored locally.

---

## 6. Migration timeline (chronological)

| # | File | Table(s) | Action |
|---|---|---|---|
| 1 | `0001_01_01_000000_create_users_table.php` | `users`, `password_reset_tokens`, `sessions` | create. `users` has `uuid` (unique bigInteger), name/avatar/email(nullable)/role/address/bio/provider fields, softDeletes (`:15-47`) |
| 2 | `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` | create |
| 3 | `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | create |
| 4 | `2025_06_27_052222_create_personal_access_tokens_table.php` | `personal_access_tokens` | create (Sanctum) |
| 5 | `2025_06_28_130024_create_settings_table.php` | `settings` | create |
| 6 | `2025_07_18_122445_create_permission_tables.php` | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | create (Spatie) |
| 7 | `2025_08_27_084502_create_dynamic_pages_table.php` | `dynamic_pages` | create |
| 8 | `2026_03_08_081816_create_designs_table.php` | `designs` (create) + `users` (alter: add `phone`) | create/alter. **Adds non-nullable `phone` to existing users** (`:31`) |
| 9 | `2026_03_08_083407_create_design_elements_table.php` | `design_elements` | create |
| 10 | `2026_03_08_084503_create_design_renders_table.php` | `design_renders` | create |
| 11 | `2026_03_08_091241_create_carts_table.php` | `carts` | create (`shipping_cost` default 10, `status` default active) |
| 12 | `2026_03_08_092002_create_cart_items_table.php` | `cart_items` | create (softDeletes) |
| 13 | `2026_03_15_024747_create_contact_us_table.php` | `contact_us` (create) + `users` (alter, **no-op** — body commented `:25-26`) | create. `down()` drops a `phone` column that this migration did not add (`:38`) |
| 14 | `2026_03_15_035349_create_orders_table.php` | `orders` | create. `stripe_payment_id` initially **NOT nullable** (`:17`) |
| 15 | `2026_03_16_040626_create_order_items_table.php` | `order_items` (create) + `users` (alter: `phone` → nullable) | create/alter (`:28`) |
| 16 | `2026_03_17_024248_add_mockup_column.php` | `designs` | alter: add `mockup_image` (`:16`) |
| 17 | `2026_03_17_031646_add_and_remove_column_from_orders.php` | `orders` | alter: `stripe_payment_id`→nullable, add `printify_order_id` (`:16,18`) |
| 18 | `2026_03_23_041914_add_stripe_session_id.php` | `orders` | alter: add `stripe_session_id` (`:17`) |
| 19 | `2026_03_23_055239_cart_items_columns_nullable.php` | `cart_items` | alter: `design_id`→nullable (`:17`) |
| 20 | `2026_03_24_055239_order_items_columns_nullable.php` | `order_items`, `designs` | alter: make several columns nullable; add `veara_product_id` to both (`:20,30`) |
| 21 | `2026_03_26_100417_add_order_image.php` | `order_items` | alter: add `image` (`:17`) |

(21 migration files total; numbering above is sequence, not file count discrepancy.)

Notes:
- `designs` table originally required `printify_variant_id, product_name, product_size, product_color` (`#8 :17-21`); these were only made nullable later (`#20 :25-29`). `DesignController@saveDesign` never sets them, so the schema must be at migration #20+ for saves to succeed (see §11).
- `veara_product_id` exists as a plain nullable string column on `designs`/`order_items` (`#20`), used by `DesignController` (`:43`) and Design resources — but there is **no `veara_products` table** to reference.

---

## 7. Middleware (`app/Http/Middleware/`)

- **`AdminMiddleware.php`** (alias `admin`, `bootstrap/app.php:23`) — blocks users whose `role == 'user'`, redirecting to `/` (`:18-20`). **Calls `auth()->user()->role` without a null check** — and in `bootstrap/app.php:17` the group middleware order is `['web', 'admin', 'auth']`, i.e. `admin` runs **before** `auth`, so an unauthenticated request would hit `null->role` (see §11).
- **`JWTMiddleware.php`** (alias `jwt.verify`, `bootstrap/app.php:24`) — parses/authenticates the JWT, returns 401 JSON on invalid/expired/missing token via `ApiResponse` (`:21-38`).

No other custom middleware. Default Laravel 11 framework middleware is used unmodified (no `app/Http/Kernel.php` in L11).

---

## 8. Services & helpers

- **`app/Helpers/helpers.php`** (class `helpers`, autoloaded — `composer.json:42-44`) — static `uploadFile` (s3 or local disk), `deleteFile`, `generateTempURL` (temporary URL for S3, falls back to `->url()`) (`:11-65`).
- **`app/Helpers/PrintifyService.php`** — `createOrder(Order, ?array $shipping)`: builds a Printify order payload and POSTs to `/shops/{shopId}/orders.json` using `config('services.printify.shop_id')` + token (`:14-58`). **Currently never called** — its only call site is commented out in `WebhookController` (`:50`). Contains a hardcoded `print_provider_id => 1` and `external_id "line-item-abc-001"` (`:26-27`).
- **`app/Helpers/PrintifyGetAllProducts.php`** — `allProducts($page, $type)`: GETs Printify products, filters out deleted, optional tag filter (`:13-37`). **Shop ID `21494572` is hardcoded into the URL** (`:11`). Contains a large commented-out `PrintifyService` class (`:43-58`).
- **`app/Helpers/PrintifyGetOneProduct.php`** — `oneProduct($product_id)`: GETs a single Printify product. **Shop ID `21494572` hardcoded** (`:11`).
- **`app/Traits/ApiResponse.php`** — `sendResponse()` / `sendError()` standard JSON envelope (`status/message/code/data/token/pagination`) (`:8-53`).
- **`app/Enum/Role.php`** — backed enum: `ADMIN=admin, USER=user, MODERATOR=moderator, SUPER_ADMIN=super_admin` (`:7-10`).
- **Mailables:** `app/Mail/NotifyUser.php` (markdown `email.notify_user_mail`, `:30`) and `app/Mail/Api/V1/SendOtpMail.php` (markdown `email.otp_mail`, `:25`).
- **`app/Providers/AppServiceProvider.php`** — registers the Socialite `apple` driver from `services.apple` config (`:24-33`).
- **View Components:** `AppLayout`, `GuestLayout`, `ImageUpload` (Breeze defaults), and `Backend/Setting/ImageUpload` (custom; renders `components.backend.setting.image-upload`, `:29-32`).
- **Form Requests:** `app/Http/Requests/Api/V1/**` (Auth, Cart, ContactUs, Design, Payment, Profile validators) + Breeze `Auth/LoginRequest` + legacy `ProfileUpdateRequest`.
- **API Resources:** ~17 under `app/Http/Resources/Api/V1/**` (Auth, Cart, ContactUs, Design, Payment, Products, Profile). Not deep-analyzed.

No `app/Services/` directory exists — service logic lives under `app/Helpers/`. No custom traits beyond `ApiResponse`.

---

## 9. View inventory (`resources/views/`)

**Breeze auth (`auth/`):** `app`, `login`, `register`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `partials/script`, `partials/style`.

**Admin backend (`backend/`):**
- Top-level: `app.blade.php`, `dashboard.blade.php`.
- `layout/`: `ContactUs/index`, `dynamic_pages/{create,edit,index}`, `Order_details/index`, `Orders/index`, `Permission/index`, `Profile/index`, `Role/{edit,index}`, `Setting/{index,printify_index,smtp_index,stripe_index}`, `stripe/{cancel,success}`, `User/index`.
- `partials/`: `brand_logo`, `contact_us/{add,index_main}`, `dynamic_pages/{create_main,edit_main,index_main}`, `footer`, `header`, `notification`, `order_details/index_main`, `orders/{add,index_main}`, `Permisson/{add_modal,index_main}`, `profile/{email_edit,information_change,password_change}`, `Role/{add_role_modal,index_main}`, `script`, `scroll`, `search`, `setting/{printify_content,smtp_content,stripe_content}`, `sidebar`, `style`, `theme_mode`, `user_profile`, `user/{add,index_main}`.

**Shared UI components (`components/`):** `application-logo`, `auth-session-status`, `backend/setting/image-upload`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`.

**Email (`email/`):** `notify_user_mail`, `otp_mail`.

**Layouts (`layouts/`):** `app`, `guest`, `navigation`.

**Profile (`profile/`):** `edit`, `partials/{delete-user-form, update-password-form, update-profile-information-form}` (Breeze).

**Root:** `dashboard.blade.php`, `welcome.blade.php`.

The `stripe/success` and `stripe/cancel` views are the Blade pages returned by the **API** `PaymentController` (`PaymentController.php:152,162,172`), i.e. the customer payment-redirect landing pages live inside the admin view tree.

---

## 10. Comparison summary vs Admin Dashboard project

| Admin Dashboard feature | Status in `veara-backend` | Detail / file paths |
|---|---|---|
| Admin UI for **VearaProducts, Garments, GarmentVariants** (CRUD + DataTables) | ❌ Does not have it | No `VearaProduct`/`Garment`/`GarmentVariant` models, controllers, migrations, routes, or views. Product data is fetched live from Printify (`app/Helpers/PrintifyGetAllProducts.php`, `…GetOneProduct.php`). A bare nullable `veara_product_id` **column** exists on `designs`/`order_items` (migration #20) with no backing table. |
| Admin UI for **Orders** | ✅ Has it | `Web/Backend/OrdersController.php` (`index/details/detailsData`); views `backend/layout/Orders/index`, `Order_details/index`; routes `backend.php:87-91`. |
| Admin UI for **Users** | ✅ Has it | `Web/Backend/UserController.php` (CRUD + soft delete + welcome mail); view `backend/layout/User/index`; routes `backend.php:45-52`. (Route declares `user.show` but the method is missing — §11.) |
| Admin UI for **Roles** | ✅ Has it | `Web/Backend/RoleController.php` (CRUD w/ permission assignment, Spatie); view `backend/layout/Role/{index,edit}`; routes `backend.php:56-63`. (Route declares `roles.add`→`create` but the method is missing — §11.) |
| Admin UI for **Permissions** | 🟡 Partial | `Web/Backend/PermissionController.php@index` returns **all permissions as JSON only** (`:14-31`) — no management UI of its own. Role↔user assignment lives in `UserRoleManagementController` (`backend.php:70-73`) + `backend/layout/Permission/index`. Permissions are seeded statically (`PermissionSeeder.php`), not editable via UI. |
| Admin UI for **Settings** | ✅ Has it | `Web/Backend/SettingController.php` (system/SMTP/Stripe/Printify); views `backend/layout/Setting/*`; routes `backend.php:27-41`. |
| Admin UI for **ContactUs** | 🟡 Partial | `Web/Backend/ContactUsController.php@index` (DataTables list + view `backend/layout/ContactUs/index`) — **read/list only**, no mark-read/delete actions despite a `read` column on the table (`create_contact_us_table.php:20`). |
| Admin UI for **DynamicPages** | ✅ Has it | `Web/Backend/DynamicPageController.php` (full CRUD); views `backend/layout/dynamic_pages/*`; routes `backend.php:78-85`. |
| Customer **JWT API at `/api/v1/*`** (Cart, Design, Payment, Product) | ✅ Has it | `routes/api.php`; `API/V1/{Cart,Design,Payment,Product}Controller.php`; JWT guard (`config/auth.php:43-46`) + `JWTMiddleware`. |
| **v2 product API at `/api/v2/products/*`** | ❌ Does not have it | No `v2` prefix anywhere in `routes/` (only `v1`). |
| **Stripe** webhook handler | 🟡 Partial | `WebhookController@stripe` exists with signature verification (`:24-32`) and marks order paid (`:60-65`), but **Printify order creation is commented out** (`:50,63`) — so payment succeeds with no fulfillment. |
| **Printify** webhook handler | 🟡 Partial | `WebhookController@printify` exists (`:79-95`) but matches on `printify_order_id` which is never populated (because Printify order creation is disabled); no signature verification; unchecked null `$order` (`:89-90`). |
| **Spatie laravel-permission** setup | ✅ Has it | `spatie/laravel-permission: 6.17` (`composer.json:21`); `config/permission.php` (`teams=false`, `:134`); migration #6; `User` uses `HasRoles` (`User.php:17`); `PermissionSeeder` seeds 6 permissions. |
| **Laravel Breeze auth scaffolding** for admin login | ✅ Has it | `laravel/breeze` (`composer.json:31`); `app/Http/Controllers/Auth/*` (9 controllers); `resources/views/auth/*`; `routes/auth.php` (register routes commented out, `:15-18`). |
| **PrintifyService helper** | ✅ Has it (idle) | `app/Helpers/PrintifyService.php@createOrder` exists but is **never invoked** (call site commented in `WebhookController:50`). Also two separate read helpers `PrintifyGetAllProducts`/`PrintifyGetOneProduct`. |

---

## 11. Findings — surprises worth flagging

1. **The admin UI already lives in `veara-backend`.** `routes/backend.php` exists and is mounted at `/admin` (`bootstrap/app.php:16-19`), with 11 `Web/Backend/*` controllers and a full `resources/views/backend/*` tree. This is contrary to the framing that the admin dashboard is only in the separate contractor project. What is **missing** here vs that project is the product-catalog admin (VearaProducts/Garments/GarmentVariants) and the v2 product API.

2. **Stripe payment has no fulfillment.** Printify order creation is commented out in the Stripe webhook (`WebhookController.php:50,63`) and the `PrintifyService` dependency is commented out of the method signature (`:18`). Orders are marked `paid` but never sent to Printify. The Printify webhook (`:79-95`) then can never match an order because `printify_order_id` is never set.

3. **`PaymentController@success()` likely throws at runtime.** It calls `Session::retrieve($sessionId)` (`:133`) but imports `Illuminate\Support\Facades\Session` (`:17`) — Laravel's session facade has no `retrieve()` method. It should be `\Stripe\Checkout\Session`. The `catch` block then renders the success view with an error (`:162-166`).

4. **Admin middleware ordering / null deref.** The admin group is `['web', 'admin', 'auth']` (`bootstrap/app.php:17`) — `admin` runs before `auth`. `AdminMiddleware` dereferences `auth()->user()->role` (`AdminMiddleware.php:18`) with no null guard, so an unauthenticated hit to `/admin/*` can fatal on `null->role`.

5. **Routes referencing non-existent controller methods:**
   - `user.show` → `UserController@show` (`backend.php:48`) — **no `show()` method** in `UserController.php`.
   - `roles.add` → `RoleController@create` (`backend.php:58`) — **no `create()` method** in `RoleController.php`.
   Both will 500 if hit (and may surface in `route:list` only as declared, not as errors).

6. **OTP / email verification is disabled.** `signup` skips OTP and creates the user immediately (`AuthenticationController.php:50-62`); the OTP cache+mail code is commented out (`:33-48`). `forgotPasswordEmail` hardcodes the reset code to `1111` (`:135`) and returns it directly in the API response body (`:143`) — a security concern in production.

7. **`SocialLogin` undefined-variable risk** when `userFromToken` returns falsy: `$user`, `$token`, `$isNewUser` are only assigned inside the `if` block (`SocialAuthController.php:29-55`) but used afterward (`:57`).

8. **Migration ↔ code drift on `designs`.** `DesignController@saveDesign` (`:41-48`) inserts only `user_id, veara_product_id, printify_product_id, mockup_image, print_files, created_by`. The original `designs` schema (`#8 :17-21`) made `printify_variant_id, product_name, product_size, product_color` NOT NULL; only migration #20 (`:25-29`) made them nullable. Saves therefore require migration #20 to have run. If a database is at an earlier migration state, `saveDesign` fails.

9. **`design_elements` table + `DesignElements` model are dead.** The table is created (`#9`) and the model exists, but no controller ever writes design elements — `saveDesign` persists only `Design` + `DesignRender` rows. Individual text/image element data (positions, fonts, colors in the migration) is never captured.

10. **`DesignController@index` is not user-scoped.** It returns `Design::with('designImages')->get()` — all designs for all users (`:25`) — even though it sits behind JWT auth and computes `$user_id` (`:23`) which is then unused.

11. **Runtime `.env` writes.** `SettingController` rewrites SMTP/Stripe/Printify keys into the `.env` file on disk (`:88-111,149-171,194-215`). On Cloud Run (ephemeral, read-mostly container filesystem) these writes won't persist across instances/restarts and may fail entirely.

12. **Hardcoded Printify shop ID.** `PrintifyGetAllProducts.php:11` and `PrintifyGetOneProduct.php:11` hardcode shop `21494572` in the URL, while `PrintifyService.php:16` reads `config('services.printify.shop_id')`. Inconsistent sourcing.

13. **Env vars read by config but absent from `.env.example`.** `.env.example` lists only the stock Laravel keys (no Stripe/Printify/JWT/Google/Apple/Frontend keys), yet config reads: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `PRINTIFY_BEARER_TOKEN`, `PRINTIFY_SHOP_ID`, `GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI`, `APPLE_CLIENT_ID/SECRET/REDIRECT_URI`, `JWT_SECRET` (+ other `JWT_*`), `FRONTEND_URL`, `FILE_SYSTEM`, `AUTH_GUARD`, `AUTH_MODEL` (`config/services.php:38-59`, `config/jwt.php:18`, `config/auth.php:17,69`). A fresh `.env` copied from the example would be missing all integration credentials.

14. **DB driver ambiguity.** `.env.example` says `sqlite` (`:22`); the Dockerfile installs Postgres extensions (`Dockerfile:15`) but also creates a sqlite file (`:28`). Intended production driver is not pinned by any committed config.

15. **`Order` migration history.** `orders.stripe_payment_id` started NOT NULL (`#14 :17`) but `PaymentController@checkout` creates the order without it (`PaymentController.php:75-80`); only migration #17 (`:16`) made it nullable. Same migration-state dependency as finding #8.

16. **`create_contact_us_table` `down()` drops a column it never added** (`#13 :38` drops `users.phone`), and its `up()` users-table alter is a commented no-op (`:25-26`) — a rollback here would corrupt the `users` schema.

17. **Sanctum is installed but unused.** `personal_access_tokens` table + `config/sanctum.php` exist, but the API authenticates via JWT (`config/auth.php:43-46`). Dead dependency surface.

18. **`CartSeeder` is never called.** `DatabaseSeeder` runs only `UserSeeder` + `PermissionSeeder` (`DatabaseSeeder.php:23-24`); `CartSeeder` (which references hardcoded `user_id` 2/3 and `design_id` 1-4) must be run manually and assumes data that the default seed does not create.

19. **`OrderItem::design()` uses `hasOne`** (`OrderItem.php:18`) where the foreign keys imply the design is referenced **by** the order item (`order_items.design_id`), i.e. it should be `belongsTo`. The relation as written will query `designs.order_item_id`, which doesn't exist.

---

## 12. Open questions

1. **Intended production DB driver** — sqlite (per `.env.example`) or Postgres (per Dockerfile extensions)? The committed config doesn't pin it; the actual Cloud Run `.env`/env vars aren't in the repo.
2. **Is the disabled Printify fulfillment intentional** (MVP "payments-only" launch) or an unfinished feature left commented (`WebhookController.php:50,63`)? Determines whether the merge needs to re-enable `PrintifyService::createOrder`.
3. **`veara_product_id` semantics** — what is the source of truth for VEARA's own product IDs given there's no `veara_products` table here? Is the catalog meant to come from the Admin Dashboard project (the VearaProducts/Garments models), making that data the thing to absorb?
4. **Which products API is canonical** — this backend's live-Printify `/api/v1/products/*`, or the Admin Dashboard's `/api/v2/products/*`? They appear to be different generations.
5. **Admin auth model** — admin login uses Breeze sessions while customers use JWT; both resolve against the same `users` table and `role` column. Is the eventual merged admin expected to keep Breeze, or move to the dashboard project's auth?
6. **Did `php artisan migrate` actually run to the latest migration in production?** Several controllers depend on the later nullable-column migrations (#17, #20). The migration state of the live database cannot be determined from files alone.
7. **`SettingController` runtime `.env` writes** — is there a persistent volume, or should credentials move to Cloud Run secret env vars before merge (since file writes won't survive on Cloud Run)?
8. **Are the missing `UserController@show` / `RoleController@create` methods** dead routes to delete, or unfinished features to implement?

---

*End of inventory.*
