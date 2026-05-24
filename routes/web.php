<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminGameController;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    //Ruta webhook Stripe
Route::post('/stripe/webhook', function (\Illuminate\Http\Request $request) {

    Log::info('WEBHOOK STRIPE ENTRÓ');

    Log::info('DB CONFIG ACTUAL', [
        'default' => config('database.default'),
        'url' => config('database.connections.pgsql.url'),
        'host' => config('database.connections.pgsql.host'),
        'database' => config('database.connections.pgsql.database'),
    ]);

    try {
        return app(\Laravel\Cashier\Http\Controllers\WebhookController::class)
            ->handleWebhook($request);

    } catch (\Throwable $e) {

        Log::error('WEBHOOK ERROR', [
            'message' => $e->getMessage(),
            'db_url_env' => env('DATABASE_URL'),
        ]);

        throw $e;
    }
});