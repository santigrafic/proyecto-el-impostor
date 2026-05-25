<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
{
    try {
        $payload = json_decode($request->getContent(), true);

        return response()->json([
            'ok' => true,
            'type' => $payload['type'] ?? null,
            'stripe_customer' => $payload['data']['object']['customer'] ?? null,
            'subscription_id' => $payload['data']['object']['id'] ?? null,
            'status' => $payload['data']['object']['status'] ?? null,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
            'payload_raw' => $request->getContent(),
        ], 500);
    }
}

    private function handleSubscriptionCreated(array $payload)
    {
        $data = $payload['data']['object'];

        // Buscar usuario por stripe_id
        $user = DB::table('users')
            ->where('stripe_id', $data['customer'])
            ->first();

        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        $firstItem = $data['items']['data'][0] ?? null;

        if (!$firstItem) {
            throw new \Exception('Subscription sin items');
        }

        // Crear subscription
        $existing = DB::table('subscriptions')
            ->where('stripe_id', $data['id'])
            ->first();

        if ($existing) {
            return; // ya existe, Stripe reintento
        }

        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'user_id' => $user->id,
            'type' => $data['metadata']['type'] ?? 'default',
            'stripe_id' => $data['id'],
            'stripe_status' => $data['status'],
            'stripe_price' => $firstItem['price']['id'],
            'quantity' => $firstItem['quantity'] ?? 1,
            'trial_ends_at' => isset($data['trial_end'])
                ? Carbon::createFromTimestamp($data['trial_end'])
                : null,
            'ends_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear items
        foreach ($data['items']['data'] as $item) {

            DB::table('subscription_items')->insert([
                'subscription_id' => $subscriptionId,
                'stripe_id' => $item['id'],
                'stripe_product' => $item['price']['product'],
                'stripe_price' => $item['price']['id'],
                'quantity' => $item['quantity'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function handleSubscriptionUpdated(array $payload)
    {
        $data = $payload['data']['object'];

        DB::table('subscriptions')
            ->where('stripe_id', $data['id'])
            ->update([
                'stripe_status' => $data['status'],
                'updated_at' => now(),
            ]);
    }

    private function handleSubscriptionDeleted(array $payload)
    {
        $data = $payload['data']['object'];

        DB::table('subscriptions')
            ->where('stripe_id', $data['id'])
            ->update([
                'ends_at' => now(),
                'stripe_status' => 'canceled',
                'updated_at' => now(),
            ]);
    }
}
