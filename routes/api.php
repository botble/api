<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Api\Http\Controllers',
    'middleware' => ['api'],
], function () {
    Route::post('register', 'AuthenticationController@register');
    Route::post('login', 'AuthenticationController@login');

    Route::post('email/check', 'AuthenticationController@checkEmail');

    Route::post('password/forgot', 'ForgotPasswordController@sendResetLinkEmail');

    Route::post('resend-verify-account-email', 'VerificationController@resend');

    // Device token management (public endpoints)
    Route::post('device-tokens', 'DeviceTokenController@store');

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('logout', 'AuthenticationController@logout');
        Route::get('me', 'ProfileController@getProfile');
        Route::put('me', 'ProfileController@updateProfile');
        Route::post('update/avatar', 'ProfileController@updateAvatar');
        Route::put('update/password', 'ProfileController@updatePassword');

        // Settings endpoints
        Route::get('settings', 'ProfileController@getSettings');
        Route::put('settings', 'ProfileController@updateSettings');

        // Device token management (authenticated endpoints)
        Route::get('device-tokens', 'DeviceTokenController@index');
        Route::put('device-tokens/{id}', 'DeviceTokenController@update')->wherePrimaryKey();
        Route::delete('device-tokens/by-token', 'DeviceTokenController@destroyByToken');
        Route::delete('device-tokens/{id}', 'DeviceTokenController@destroy')->wherePrimaryKey();
        Route::post('device-tokens/{id}/deactivate', 'DeviceTokenController@deactivate')->wherePrimaryKey();

        // Notifications (authenticated endpoints)
        Route::get('notifications', 'NotificationController@index');
        Route::get('notifications/stats', 'NotificationController@getStats');
        Route::post('notifications/mark-all-read', 'NotificationController@markAllAsRead');
        Route::post('notifications/{id}/read', 'NotificationController@markAsRead')->wherePrimaryKey();
        Route::post('notifications/{id}/clicked', 'NotificationController@markAsClicked')->wherePrimaryKey();
        Route::delete('notifications/{id}', 'NotificationController@destroy')->wherePrimaryKey();
    });
});
