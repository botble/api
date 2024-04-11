<?php

use Botble\Api\Http\Controllers\ApiController;
use Botble\Api\Http\Controllers\SanctumTokenController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::name('api.')->prefix('settings/api')->group(function () {
        Route::prefix('sanctum-token')->name('sanctum-token.')->group(function () {
            Route::resource('/', SanctumTokenController::class)
                ->parameters(['' => 'sanctum-token'])
                ->except('edit', 'update', 'show');
        });

        Route::group(['permission' => 'api.settings'], function () {
            Route::get('/', [ApiController::class, 'edit'])->name('settings');
            Route::put('/', [ApiController::class, 'update'])->name('settings.update');
        });
    });
});
