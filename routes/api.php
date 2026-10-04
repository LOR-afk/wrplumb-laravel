<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HrQuotationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'WR Plumbing API is running',
    ]);
});

Route::prefix('auth')->group(function () {
    Route::get('/captcha', [AuthController::class, 'captcha']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);

        Route::post(
            '/admin/verify-otp',
            [AuthController::class, 'verifyAdminOtp']
        );

        Route::post(
            '/admin/resend-otp',
            [AuthController::class, 'resendAdminOtp']
        );

        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/hr/quotations',
        [HrQuotationController::class, 'index']
    );
});