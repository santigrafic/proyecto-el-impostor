<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminGameController;

Route::get('/', function () {
    return view('welcome');
});

//Login
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->prefix('admin')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->prefix('admin')
    ->name('login');

    //Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

    //Rutas web
Route::middleware('auth', 'role:admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        Route::resource('users', AdminUserController::class);
        Route::resource('games', AdminGameController::class);
    });