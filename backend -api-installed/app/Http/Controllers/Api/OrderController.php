<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, $orderNumber)
    {
        $order = Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json($order);
    }

    public function track($orderNumber)
    {
        $order = Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json($order);
    }

    public function guestCheckout(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
            'guest_phone' => 'required|string|max:20',
            'delivery_address' => 'required|array',
            'delivery_address.street' => 'required|string',
            'delivery_address.city' => 'required|string',
            'delivery_address.state' => 'required|string',
            'delivery_address.zip' => 'required|string',
            'payment_method' => 'required|in:stripe,paypal,cod',
            'notes' => 'nullable|string',
        ]);

        $cartItems = CartItem::with('product')
            ->where('session_id', $request->session_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        return $this->processOrder($request, null, $cartItems);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'delivery_address' => 'required|array',
            'delivery_address.street' => 'required|string',
            'delivery_address.city' => 'required|string',
            'delivery_address.state' => 'required|string',
            'delivery_address.zip' => 'required|string',
            'payment_method' => 'required|in:stripe,paypal,cod',
            'notes' => 'nullable|string',
        ]);

        $cartItems = CartItem::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        return $this->processOrder($request, $request->user(), $cartItems);
    }

    private function processOrder(Request $request, $user, $cartItems)
    {
        return DB::transaction(function () use ($request, $user, $cartItems) {
            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $delivery_fee = $subtotal >= 50 ? 0 : 4.99;
            $tax = round($subtotal * 0.08, 2);
            $total = round($subtotal + $delivery_fee + $tax, 2);

            // Validate stock
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    return response()->json([
                        'message' => "Insufficient stock for {$item->product->name}"
                    ], 422);
                }
            }

            $orderData = [
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery_fee,
                'tax' => $tax,
                'total' => $total,
                'notes' => $request->notes,
                'estimated_delivery' => now()->addDays(1),
            ];

            if ($user) {
                $orderData['user_id'] = $user->id;
            } else {
                $orderData['guest_name'] = $request->guest_name;
                $orderData['guest_email'] = $request->guest_email;
                $orderData['guest_phone'] = $request->guest_phone;
            }

            $order = Order::create($orderData);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ]);

                // Decrement stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cartItems->each->delete();

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('items.product'),
                'order_number' => $order->order_number,
            ], 201);
        });
    }

    public function cancel(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 422);
        }

        $order->update(['status' => 'cancelled']);

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return response()->json(['message' => 'Order cancelled', 'order' => $order]);
    }
}
