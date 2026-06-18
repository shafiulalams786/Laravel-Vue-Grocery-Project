<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\GuestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Guest session cart
    Route::prefix('guest')->group(function () {
        Route::post('/session', [GuestController::class, 'initSession']);
        Route::get('/cart/{sessionId}', [CartController::class, 'guestIndex']);
        Route::post('/cart/{sessionId}', [CartController::class, 'guestAdd']);
        Route::put('/cart/{sessionId}/{itemId}', [CartController::class, 'guestUpdate']);
        Route::delete('/cart/{sessionId}/{itemId}', [CartController::class, 'guestRemove']);
        Route::delete('/cart/{sessionId}', [CartController::class, 'guestClear']);
    });

    // Guest checkout
    Route::post('/checkout/guest', [OrderController::class, 'guestCheckout']);

    // Payment routes (for webhooks & PayPal redirects)
    Route::post('/payment/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
    Route::get('/payment/paypal/success', [PaymentController::class, 'paypalSuccess']);
    Route::get('/payment/paypal/cancel', [PaymentController::class, 'paypalCancel']);

    // Payment intent creation (accessible by guests)
    Route::post('/payment/stripe/intent', [PaymentController::class, 'createStripeIntent']);
    Route::post('/payment/paypal/order', [PaymentController::class, 'createPaypalOrder']);
    Route::post('/payment/paypal/capture', [PaymentController::class, 'capturePaypalOrder']);

    // Order tracking (by order number, no auth)
    Route::get('/orders/track/{orderNumber}', [OrderController::class, 'track']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        // Authenticated cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'add']);
        Route::put('/cart/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/{itemId}', [CartController::class, 'remove']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::post('/cart/merge', [CartController::class, 'mergeGuestCart']);

        // Authenticated checkout
        Route::post('/checkout', [OrderController::class, 'checkout']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
        Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);

    });

});

// ── Admin Routes ──────────────────────────────────────────────────────────────
// Loaded here so they share the same 'api' middleware group (prefix: /api)
// and are NOT caught by web.php's catch-all.
// admin.php adds its own prefix('admin') → final path: /api/admin/...
require __DIR__ . '/admin.php';
