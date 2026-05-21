<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGameToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $GAME_API_TOKEN = "Xon8uzJQxMwUjwntM7K3I1wOry4XDKX3l06SvPLq7Vwhg0vE7ma0Z8NBYethSLrV";
        
        $token = $request->header('X-GAME-TOKEN');

        if (!$token || $token !== $GAME_API_TOKEN) {
            return response()->json([
                'error' => 'Unauthorized game request',
                'debug' => [
                    'header' => $token,
                    // 'config' => config('services.game_token')
                    'config' => $GAME_API_TOKEN
                ]
            ], 403);
        }

        return $next($request);
    }
}
