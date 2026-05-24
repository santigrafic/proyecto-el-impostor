<?php

use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StripeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;

// Unirse a una room
Route::post('/rooms/{roomId}/join', [RoomController::class, 'join']);

// Ver estado de la room
Route::get('/rooms/{roomId}', [RoomController::class, 'show']);

// Crear room
Route::post('/rooms', [RoomController::class, 'create']);

// Iniciar partida
// Obtener info de cada jugador
Route::get('/rooms/{roomId}/me', [RoomController::class, 'me']);

// Devuelve estado de la partida a todos los jugadores
Route::get('/rooms/{roomId}/state', [RoomController::class, 'state']);

// Un jugador sale de la room
Route::post('rooms/{roomId}/exit', [RoomController::class, 'exitRoom']);

//Game
Route::prefix('games')->group(function () {
    Route::get('{roomId}/state', [GameController::class, 'state']);
    Route::post('{roomId}/me', [GameController::class, 'me']);
    Route::post('{roomId}/word', [GameController::class, 'playWord']);
    Route::post('{roomId}/exit', [GameController::class, 'exitGame']);
});

Route::post('/games/{roomId}/start-voting', [GameController::class, 'startVoting']);
Route::post('/games/{roomId}/vote', [GameController::class, 'vote']);
Route::get('/games/{roomId}/results', [GameController::class, 'results']);

// Rutas CRUD Games
// Route::get('/games', [GameController::class, 'index']);
// Route::get('/games/{id}', [GameController::class, 'show']);
Route::middleware('game.token')->group(function () {
    //Route::post('/games', [GameController::class, 'store']);
    Route::post('/rooms/{roomId}/start', [RoomController::class, 'start']);
    Route::post('/games/{roomId}/finish', [GameController::class, 'finish']);
});

// Rutas Usuarios
Route::apiResource('users', UserController::class);
Route::get('ranking', [UserController::class, 'ranking']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/update', [UserController::class, 'update']);
    Route::delete('/destroy', [UserController::class, 'destroy']);
    Route::get('me/pdf', [UserController::class, 'profilePdf']);
    Route::post('/create-checkout-session', [StripeController::class, 'createSession']); // Subscription
});

// Login
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Register
Route::post('/register', [AuthController::class, 'register']);


// Ruta par hacer ping en render
/*Route::get('/api/ping', function () {
    return response()->json(['ok' => true]);
});*/

// Ruta webhook Stripe
Route::post('/stripe/webhook', function () {

    return response()->json([
        'default_connection' => config('database.default'),
        'db_host' => config('database.connections.mysql.host'),
        'env_connection' => env('DB_CONNECTION'),
        'database_url' => env('DATABASE_URL'),
    ]);
});