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

            switch ($payload['type']) {

                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($payload);
                    break;

                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($payload);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($payload);
                    break;
            }

            return response()->json([
                'success' => true
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function handleSubscriptionCreated(array $payload)
{
    $data = $payload['data']['object'] ?? null;

    if (!$data) {
        throw new \Exception('Payload inválido: falta data.object');
    }

    // 1. Buscar usuario por stripe_id
    $user = DB::table('users')
        ->where('stripe_id', $data['customer'])
        ->first();

    if (!$user) {
        // IMPORTANTE: no romper producción por esto
        throw new \Exception('Usuario no encontrado para customer: ' . $data['customer']);
    }

    // 2. Validar items
    $firstItem = $data['items']['data'][0] ?? null;

    if (!$firstItem) {
        throw new \Exception('Subscription sin items');
    }

    // 3. IDEMPOTENCIA (Stripe reintenta SIEMPRE)
    $existing = DB::table('subscriptions')
        ->where('stripe_id', $data['id'])
        ->first();

    if ($existing) {
        return; // ya procesado
    }

    // 4. Insert subscription
    $subscriptionId = DB::table('subscriptions')->insertGetId([
        'user_id' => $user->id,
        'type' => $data['metadata']['type'] ?? 'default',
        'stripe_id' => $data['id'],
        'stripe_status' => $data['status'],
        'stripe_price' => $firstItem['price']['id'] ?? null,
        'quantity' => $firstItem['quantity'] ?? 1,
        'trial_ends_at' => !empty($data['trial_end'])
            ? Carbon::createFromTimestamp($data['trial_end'])
            : null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 5. Insert items (seguro)
    foreach (($data['items']['data'] ?? []) as $item) {

        DB::table('subscription_items')->insert([
            'subscription_id' => $subscriptionId,
            'stripe_id' => $item['id'],
            'stripe_product' => $item['price']['product'] ?? null,
            'stripe_price' => $item['price']['id'] ?? null,
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
