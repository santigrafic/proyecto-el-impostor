<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    /*public function handleWebhook(Request $request)
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
    }*/

    // DEBUG
    public function handleWebhook(Request $request)
    {
        try {
            $payload = json_decode($request->getContent(), true);

            return response()->json([
                'ok' => true,
                'type' => $payload['type'] ?? null,

                // 🔍 DEBUG STRIPE
                'stripe_customer' => $payload['data']['object']['customer'] ?? null,
                'subscription_id' => $payload['data']['object']['id'] ?? null,
                'status' => $payload['data']['object']['status'] ?? null,

                // 🔍 DEBUG DB / ENTORNO
                'db_default' => config('database.default'),
                'mysql_host' => config('database.connections.mysql.host') ?? null,
                'mysql_db' => config('database.connections.mysql.database') ?? null,
                'app_env' => config('app.env'),

                // 🔍 DEBUG RAW (muy importante)
                'raw_keys' => array_keys($payload),
                'has_data' => isset($payload['data']),
                'has_object' => isset($payload['data']['object']),
                'raw_length' => strlen($request->getContent()),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function handleSubscriptionCreated(array $payload)
    {
        $data = $payload['data']['object'] ?? [];

        DB::beginTransaction();

        try {

            $user = DB::table('users')
                ->where('stripe_id', $data['customer'] ?? null)
                ->first();

            if (!$user) {
                DB::rollBack();
                return; // no rompemos webhook
            }

            $firstItem = $data['items']['data'][0] ?? null;

            if (!$firstItem) {
                DB::rollBack();
                return;
            }

            // evitar duplicados
            $exists = DB::table('subscriptions')
                ->where('stripe_id', $data['id'])
                ->exists();

            if ($exists) {
                DB::rollBack();
                return;
            }

            $subscriptionId = DB::table('subscriptions')->insertGetId([
                'user_id' => $user->id,
                'type' => $data['metadata']['type'] ?? 'default',
                'stripe_id' => $data['id'],
                'stripe_status' => $data['status'],
                'stripe_price' => $firstItem['price']['id'] ?? null,
                'quantity' => $firstItem['quantity'] ?? 1,
                'trial_ends_at' => isset($data['trial_end'])
                    ? Carbon::createFromTimestamp($data['trial_end'])
                    : null,
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['items']['data'] ?? [] as $item) {
                DB::table('subscription_items')->insert([
                    'subscription_id' => $subscriptionId,
                    'stripe_id' => $item['id'] ?? null,
                    'stripe_product' => $item['price']['product'] ?? null,
                    'stripe_price' => $item['price']['id'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // MUY IMPORTANTE: no romper webhook
            return;
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
