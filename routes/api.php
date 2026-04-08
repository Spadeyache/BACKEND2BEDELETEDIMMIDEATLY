<?php

use App\Http\Controllers\API\Auth\User\ProfileController;
use App\Http\Controllers\API\Auth\User\SocialAuthController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\ContactUsController;
use App\Http\Controllers\API\V1\DesignController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\User\AuthenticationController;

//! Route for user
Route::prefix('v1')->controller(AuthenticationController::class)->group(function () {
    Route::post('/sign-up', 'signup');
    Route::post('/verify/otp', 'verifyOtp');
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'forgotPasswordEmail');
    Route::post('/forgot-password/verifyOtp', 'forgotPasswordVerifyOtp');
    Route::post('/reset-password', 'resetPassword');
});


// Route for user social auth
Route::post('v1/social-login', [SocialAuthController::class, 'SocialLogin']);

Route::prefix('v1/products')->controller(ProductController::class)->group(function () {
//     Route::get('get-all', 'index');
//     Route::get('get-one/{id}', 'productDetail');
    Route::get('/catalog-data', 'productTags');
});

Route::post('v1/contact-us', [ContactUsController::class, 'store']);


Route::group(['middleware' => ['jwt.verify']], function () {
    /**
     * Profile
     */
    Route::prefix('v1/auth')->controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index');
        Route::put('/profile', 'update');
        Route::post('/logout', 'logout');
        Route::post('/change-password', 'changePassword');
    });

    Route::prefix('v1/products')->controller(CartController::class)->group(function () {
        Route::post('/add-to-cart', 'addToCart');
        Route::get('/my-orders', 'myOrders');
        Route::get('/cart-details', 'cartDetails');
        Route::delete('/remove-from-cart/{id}', 'removeFromCart');
        Route::post('/update-quantity', 'updateQuantity');
    });
    
    Route::prefix('v1/payment')->controller(PaymentController::class)->group(function () {
        Route::get('/checkout/{id}', 'index');
        Route::post('/checkout/initiate', 'checkout');
    });

    Route::prefix('v1/designs')->controller(DesignController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/save', 'saveDesign');
    });

    Route::prefix('v1/products')->controller(ProductController::class)->group(function () {
        Route::get('get-all', 'index');
        Route::get('get-one/{id}', 'productDetail');
        // Route::get('/catalog-data', 'productTags');
        Route::get('show-just-designed-product/{id}', 'justDesignedProduct');
    });
    
});

Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

// Webhooks — NO auth middleware, but signature-verified
Route::prefix('v1/webhooks')->controller(WebhookController::class)->group(function () {
    Route::post('stripe', 'stripe');
    Route::post('printify', 'printify');
});
