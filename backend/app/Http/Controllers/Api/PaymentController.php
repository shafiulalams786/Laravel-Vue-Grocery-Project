<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ─── STRIPE ──────────────────────────────────────────────────────────

    public function createStripeIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'order_number' => 'nullable|string',
        ]);

        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round($request->amount * 100), // Convert to cents
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_number' => $request->order_number ?? '',
                ],
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Webhook error'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $intent = $event->data->object;
                $orderNumber = $intent->metadata->order_number ?? null;

                if ($orderNumber) {
                    Order::where('order_number', $orderNumber)->update([
                        'payment_status' => 'paid',
                        'payment_id' => $intent->id,
                        'status' => 'confirmed',
                    ]);
                }
                break;

            case 'payment_intent.payment_failed':
                $intent = $event->data->object;
                $orderNumber = $intent->metadata->order_number ?? null;

                if ($orderNumber) {
                    Order::where('order_number', $orderNumber)->update([
                        'payment_status' => 'failed',
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }

    // ─── PAYPAL ──────────────────────────────────────────────────────────

    public function createPaypalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'order_number' => 'nullable|string',
        ]);

        try {
            $accessToken = $this->getPaypalAccessToken();
            $paypalUrl = config('services.paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $response = \Http::withToken($accessToken)
                ->post("{$paypalUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $request->order_number ?? 'order_' . time(),
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($request->amount, 2, '.', ''),
                        ],
                    ]],
                    'application_context' => [
                        'return_url' => config('app.frontend_url') . '/checkout/success',
                        'cancel_url' => config('app.frontend_url') . '/checkout/cancel',
                    ],
                ]);

            $data = $response->json();

            if ($response->failed()) {
                throw new \Exception($data['message'] ?? 'PayPal order creation failed');
            }

            return response()->json([
                'paypal_order_id' => $data['id'],
                'approve_url' => collect($data['links'])->firstWhere('rel', 'approve')['href'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'PayPal error: ' . $e->getMessage()], 500);
        }
    }

    public function capturePaypalOrder(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'order_number' => 'nullable|string',
        ]);

        try {
            $accessToken = $this->getPaypalAccessToken();
            $paypalUrl = config('services.paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $response = \Http::withToken($accessToken)
                ->post("{$paypalUrl}/v2/checkout/orders/{$request->paypal_order_id}/capture");

            $data = $response->json();

            if ($response->failed() || $data['status'] !== 'COMPLETED') {
                throw new \Exception('PayPal capture failed');
            }

            if ($request->order_number) {
                Order::where('order_number', $request->order_number)->update([
                    'payment_status' => 'paid',
                    'payment_id' => $request->paypal_order_id,
                    'payment_data' => $data,
                    'status' => 'confirmed',
                ]);
            }

            return response()->json([
                'message' => 'Payment successful',
                'transaction_id' => $data['id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'PayPal capture error: ' . $e->getMessage()], 500);
        }
    }

    public function paypalSuccess(Request $request)
    {
        return redirect(config('app.frontend_url') . '/checkout/success?token=' . $request->token);
    }

    public function paypalCancel(Request $request)
    {
        return redirect(config('app.frontend_url') . '/checkout/cancel');
    }

    private function getPaypalAccessToken(): string
    {
        $paypalUrl = config('services.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $response = \Http::withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.client_secret')
        )->asForm()->post("{$paypalUrl}/v1/oauth2/token", [
            'grant_type' => 'client_credentials',
        ]);

        return $response->json()['access_token'];
    }
}
