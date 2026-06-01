<?php



use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\Web\Backend\ContactUsController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DesignCatalogController;
use App\Http\Controllers\Web\Backend\DynamicPageController;
use App\Http\Controllers\Web\Backend\OrdersController;
use App\Http\Controllers\Web\Backend\PermissionController;
use App\Http\Controllers\Web\Backend\ProfileController;
use App\Http\Controllers\Web\Backend\RoleController;
use App\Http\Controllers\Web\Backend\SettingController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\UserRoleManagementController;
use App\Http\Controllers\Web\Backend\VearaProductController;
use App\Http\Controllers\Web\Backend\GarmentController;
use App\Http\Controllers\Web\Backend\GarmentVariantController;

//! dashboard
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
//! profile
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile',  'index')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
    Route::patch('/password', 'passwordChange')->name('password.change');
    Route::patch('/email-update', 'updateEmail')->name('email.change');
});
//! setting
Route::controller(SettingController::class)->group(function () {
    //! account or system setting
    Route::get('/setting', 'index')->name('setting.index');
    Route::post('/setting', 'store')->name('setting.store');

    //! smtp setting
    Route::get('/smtp-setting', 'smtpIndex')->name('smtp.index');
    Route::post('/smtp-setting', 'smtpStore')->name('smtp.store');
    
    Route::get('/stripe-setting', 'stripeKeysIndex')->name('stripe.index');
    Route::post('/stripe-setting', 'stripeKeysStore')->name('stripe.store');
    
    Route::get('/printify-setting', 'printifyKeysIndex')->name('printify.index');
    Route::post('/printify-setting', 'printifyKeysStore')->name('printify.store');
});


//! users
Route::controller(UserController::class)->group(function () {
    Route::get('/user', 'index')->name('user.index');
    Route::post('/user', 'store')->name('user.store');
    Route::get('/user/{id}/show', 'show')->name('user.show');
    Route::get('/user/{id}/edit',  'edit')->name('user.edit');
    Route::put('/user/{id}/update',  'update')->name('user.update');
    Route::delete('/user/{id}/delete',  'delete')->name('user.delete');
});

//! veara products
Route::controller(VearaProductController::class)->group(function () {
    Route::get('/veara-product', 'index')->name('veara-product.index');
    Route::post('/veara-product', 'store')->name('veara-product.store');
    Route::get('/veara-product/{id}/edit',  'edit')->name('veara-product.edit');
    Route::put('/veara-product/{id}/update',  'update')->name('veara-product.update');
    Route::delete('/veara-product/{id}/delete',  'delete')->name('veara-product.delete');
});

//! garments
Route::controller(GarmentController::class)->group(function () {
    Route::get('/garment', 'index')->name('garment.index');
    Route::post('/garment', 'store')->name('garment.store');
    Route::get('/garment/{id}/edit', 'edit')->name('garment.edit');
    Route::put('/garment/{id}/update', 'update')->name('garment.update');
    Route::delete('/garment/{id}/delete', 'delete')->name('garment.delete');
    Route::get('/garment/blueprint/{id}/print-providers', 'getPrintProviders')->name('garment.print-providers');
});

//! garment variants
Route::controller(GarmentVariantController::class)->group(function () {
    Route::get('/garment-variant', 'index')->name('garment-variant.index');
    Route::post('/garment-variant', 'store')->name('garment-variant.store');
    Route::get('/garment-variant/{id}/edit', 'edit')->name('garment-variant.edit');
    Route::put('/garment-variant/{id}/update', 'update')->name('garment-variant.update');
    Route::delete('/garment-variant/{id}/delete', 'delete')->name('garment-variant.delete');
    Route::get('/garment-variant/{id}/printify-variants', 'getPrintifyVariants')->name('garment-variant.printify-variants');
});

Route::get('/design-catalog', [DesignCatalogController::class, 'index'])->name('design-catalog.index');


//! Route for role management
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'index')->name('roles.index');
    Route::get('/roles/add', 'create')->name('roles.add');
    Route::post('/role/store', 'store')->name('role.store');
    Route::get('/role/edit/{id}', 'edit')->name('role.edit');
    Route::post('/role/update/{id}', 'update')->name('role.update');
    Route::delete('/role/delete/{id}', 'destroy')->name('role.delete');
});

//! Route for permission management
Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');  // Get permissions


//! Route for user role management
Route::prefix('user')->controller(UserRoleManagementController::class)->group(function () {
    Route::get('/roles', 'index')->name('user.roles.index');
    Route::post('/{id}/attach-role', 'attachRole')->name('user.attach.role');
    Route::post('/{id}/detach-role', 'detachRole')->name('user.detach.role');
});


//! Route for dynamic page
Route::prefix('/dynamic-pages')->controller(DynamicPageController::class)->group(function () {
    Route::get('/', 'index')->name('dynamic-pages.index');
    Route::get('/create', 'create')->name('dynamic-pages.create');
    Route::post('/', 'store')->name('dynamic-pages.store');
    Route::get('/{id}/edit', 'edit')->name('dynamic-pages.edit');
    Route::post('/{id}','update')->name('dynamic-pages.update');
    Route::delete('/{id}','destroy')->name('dynamic-pages.delete');
});

Route::prefix('/orders')->controller(OrdersController::class)->group( function () {
    Route::get('/', 'index')->name('orders.index');
    Route::get('/{id}', 'details')->name('orders.details');
    Route::get('/{id}/data', 'detailsData')->name('orders.details.data');
});

Route::prefix('/contact-us')->controller(ContactUsController::class)->group( function () {
    Route::get('/', 'index')->name('contact_us.index');
});
