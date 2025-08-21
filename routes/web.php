<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callbackGoogle'])->name('api.auth.google.callback');
require __DIR__.'/auth.php';
