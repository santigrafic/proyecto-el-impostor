<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function createSession(Request $request)
    {
        $user = $request->user();

        $priceId = env('STRIPE_PREMIUM_PRICE_ID');

        $checkout = $user->newSubscription(
            'premium',
            $priceId
        )->checkout([
            'success_url' => env('LOCAL_FRONT_END') . '/premium-success?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => env('LOCAL_FRONT_END') . '/subscription',
        ]);

        return response()->json([
            'url' => $checkout->url,
        ]);
    }
}