<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('users')->name('admin.users.')->group(function () {
        // Admin user management routes will be added here
    });
});
