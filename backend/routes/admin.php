<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes  –  prefix: /api/admin
| All routes require auth:sanctum + admin middleware
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    // ── Dashboard ───────────────────────────────────────────────────────
    Route::get('/dashboard/stats',              [DashboardController::class, 'stats']);
    Route::get('/dashboard/revenue-chart',      [DashboardController::class, 'revenueChart']);
    Route::get('/dashboard/orders-by-status',   [DashboardController::class, 'ordersByStatus']);
    Route::get('/dashboard/orders-by-payment',  [DashboardController::class, 'ordersByPaymentMethod']);
    Route::get('/dashboard/top-products',       [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/low-stock',          [DashboardController::class, 'lowStockAlerts']);
    Route::get('/dashboard/recent-orders',      [DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/category-revenue',   [DashboardController::class, 'categoryRevenue']);

    // ── Orders ──────────────────────────────────────────────────────────
    Route::get('/orders',                        [OrderController::class, 'index']);
    Route::get('/orders/export',                 [OrderController::class, 'export']);
    Route::get('/orders/{id}',                   [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status',          [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{id}/payment-status',  [OrderController::class, 'updatePaymentStatus']);
    Route::post('/orders/bulk-status',           [OrderController::class, 'bulkUpdateStatus']);
    Route::delete('/orders/{id}',                [OrderController::class, 'destroy']);

    // ── Products ────────────────────────────────────────────────────────
    Route::get('/products',                      [ProductController::class, 'index']);
    Route::post('/products',                     [ProductController::class, 'store']);
    Route::get('/products/{id}',                 [ProductController::class, 'show']);
    Route::put('/products/{id}',                 [ProductController::class, 'update']);
    Route::delete('/products/{id}',              [ProductController::class, 'destroy']);
    Route::post('/products/bulk',                [ProductController::class, 'bulkAction']);
    Route::patch('/products/{id}/stock',         [ProductController::class, 'adjustStock']);

    // ── Categories ──────────────────────────────────────────────────────
    Route::get('/categories',                    [CategoryController::class, 'index']);
    Route::post('/categories',                   [CategoryController::class, 'store']);
    Route::put('/categories/{id}',               [CategoryController::class, 'update']);
    Route::delete('/categories/{id}',            [CategoryController::class, 'destroy']);
    Route::post('/categories/reorder',           [CategoryController::class, 'reorder']);

    // ── Customers ───────────────────────────────────────────────────────
    Route::get('/customers',                     [CustomerController::class, 'index']);
    Route::get('/customers/export',              [CustomerController::class, 'export']);
    Route::get('/customers/{id}',                [CustomerController::class, 'show']);
    Route::patch('/customers/{id}/ban',          [CustomerController::class, 'toggleBan']);

    // ── Coupons ─────────────────────────────────────────────────────────
    Route::get('/coupons',                       [CouponController::class, 'index']);
    Route::post('/coupons',                      [CouponController::class, 'store']);
    Route::put('/coupons/{id}',                  [CouponController::class, 'update']);
    Route::delete('/coupons/{id}',               [CouponController::class, 'destroy']);
    Route::get('/coupons/generate-code',         [CouponController::class, 'generate']);

    // ── Reports ─────────────────────────────────────────────────────────
    Route::get('/reports/sales',                 [ReportController::class, 'sales']);
    Route::get('/reports/sales/export',          [ReportController::class, 'exportSalesCsv']);
    Route::get('/reports/products',              [ReportController::class, 'products']);
    Route::get('/reports/customers',             [ReportController::class, 'customers']);

    // ── Settings ────────────────────────────────────────────────────────
    Route::get('/settings',                      [SettingsController::class, 'index']);
    Route::put('/settings',                      [SettingsController::class, 'update']);
});
