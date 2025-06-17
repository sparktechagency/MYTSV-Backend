<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\FAQController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\NotificationController;
use App\Models\Blog;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api'], function ($router) {

    Route::prefix('auth/')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('social-login', [AuthController::class, 'socialLogin']);
        Route::post('otp-verification', [AuthController::class, 'otpVerify']);
        Route::get('check-token', [AuthController::class, 'validateToken']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forget-password', [AuthController::class, 'forgetPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:api', 'verified.user'])->prefix('/')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('edit-profile', [AuthController::class, 'editProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);

        // user routes
        Route::middleware('user')->as('user')->group(function () {
            Route::resource('faqs', FAQController::class);
            Route::post('send-message', [SettingController::class, 'sendMessage']);
        });

        // admin routes
        Route::middleware('admin')->prefix('admin/')->as('admin')->group(function () {
            Route::put('about-us/{id}', [SettingController::class, 'updateAboutUs']);
            Route::post('page', [SettingController::class, 'createOrUpdatePage']);
            Route::post('contact', [SettingController::class, 'updateContact']);
            Route::resource('categories', CategoryController::class)->except('index','show');
            Route::resource('faqs', FAQController::class);
            Route::resource('blogs', BlogController::class);
        });

        // common routes
        Route::middleware('admin.user')->as('common')->group(function () {
            Route::get('notifications', [NotificationController::class, 'notifications']);
            Route::get('mark-notification/{id}', [NotificationController::class, 'singleMark']);
            Route::get('mark-all-notification', [NotificationController::class, 'allMark']);

            Route::get('about-us', [SettingController::class, 'getAboutUs']);
            Route::get('page', [SettingController::class, 'getPage']);
            Route::get('contact', [SettingController::class, 'getContact']);
        });
    });

    // token free routes
    Route::resource('categories', CategoryController::class)->only('index');
    Route::resource('blogs', BlogController::class)->only('index','show');
});
