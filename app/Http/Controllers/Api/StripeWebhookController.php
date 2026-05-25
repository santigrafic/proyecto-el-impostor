<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            $this->setMaxNetworkRetries();

            return $this->{$method}($payload);
        }

        return new Response('No handler', 200);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload ?? null,
                'db_default' => config('database.default'),
                'mysql_host' => config('database.connections.mysql.host') ?? null,
                'pgsql_host' => config('database.connections.pgsql.host') ?? null,
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        
            $stripeCustomerId = $payload['data']['object']['customer'];
        $data = $payload['data']['object'];

        // 🔥 USUARIO DIRECTO EN MYSQL (SIN CASHIER)
        $user = DB::connection('mysql')
            ->table('users')
            ->where('stripe_id', $stripeCustomerId)
            ->first();

        if (!$user) {
            return new Response('User not found', 200);
        }

        $firstItem = $data['items']['data'][0];
        $isSinglePrice = count($data['items']['data']) === 1;

        $trialEndsAt = isset($data['trial_end'])
            ? Carbon::createFromTimestamp($data['trial_end'])
            : null;

        // 🔥 SUBSCRIPTION
        DB::connection('mysql')->table('subscriptions')->updateOrInsert(
            ['stripe_id' => $data['id']],
            [
                'user_id' => $user->id,
                'type' => $data['metadata']['type']
                    ?? $data['metadata']['name']
                    ?? 'default',
                'stripe_status' => $data['status'],
                'stripe_price' => $isSinglePrice ? $firstItem['price']['id'] : null,
                'quantity' => $isSinglePrice ? ($firstItem['quantity'] ?? null) : null,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 🔥 ITEMS
        foreach ($data['items']['data'] as $item) {
            DB::connection('mysql')->table('subscription_items')->updateOrInsert(
                ['stripe_id' => $item['id']],
                [
                    'subscription_id' => DB::connection('mysql')
                        ->table('subscriptions')
                        ->where('stripe_id', $data['id'])
                        ->value('id'),

                    'stripe_product' => $item['price']['product'],
                    'stripe_price' => $item['price']['id'],
                    'quantity' => $item['quantity'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 🔥 limpiar trial usuario
        if (!is_null($user->trial_ends_at)) {
            DB::connection('mysql')
                ->table('users')
                ->where('id', $user->id)
                ->update([
                    'trial_ends_at' => null,
                    'updated_at' => now(),
                ]);
        }

        return new Response('Webhook handled', 200);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        return $this->handleCustomerSubscriptionCreated($payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        $stripeCustomerId = $payload['data']['object']['customer'];
        $subscriptionId = $payload['data']['object']['id'];

        $user = DB::connection('mysql')
            ->table('users')
            ->where('stripe_id', $stripeCustomerId)
            ->first();

        if (!$user) {
            return new Response('User not found', 200);
        }

        DB::connection('mysql')
            ->table('subscriptions')
            ->where('stripe_id', $subscriptionId)
            ->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
                'updated_at' => now(),
            ]);

        return new Response('Webhook handled', 200);
    }

    protected function handleCustomerUpdated(array $payload)
    {
        return new Response('Webhook handled', 200);
    }

    protected function handleCustomerDeleted(array $payload)
    {
        return new Response('Webhook handled', 200);
    }

    protected function setMaxNetworkRetries($retries = 3)
    {
        Stripe::setMaxNetworkRetries($retries);
    }
}