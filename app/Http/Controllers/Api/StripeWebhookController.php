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

        // 🔍 info de configuración de BD
        $dbInfo = [
            'default_connection' => config('database.default'),
            'mysql_host' => config('database.connections.mysql.host') ?? null,
            'mysql_db' => config('database.connections.mysql.database') ?? null,
            'mysql_user' => config('database.connections.mysql.username') ?? null,
        ];

        // 🔍 test real de conexión (clave)
        $dbTest = null;
        try {
            $dbTest = DB::select('SELECT 1 as ok');
        } catch (\Throwable $dbError) {
            $dbTest = [
                'error' => $dbError->getMessage(),
            ];
        }

        // 🔍 intento de leer users (solo lectura)
        $userSample = null;
        try {
            $userSample = DB::table('users')
                ->select('id', 'stripe_id')
                ->limit(3)
                ->get();
        } catch (\Throwable $e) {
            $userSample = [
                'error' => $e->getMessage(),
            ];
        }

        return response()->json([
            'ok' => true,
            'stripe_type' => $payload['type'] ?? null,

            'db_config' => $dbInfo,
            'db_test' => $dbTest,
            'user_sample' => $userSample,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'fatal_error' => $e->getMessage(),
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
