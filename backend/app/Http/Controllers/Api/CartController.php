<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Authenticated cart
    public function index(Request $request)
    {
        $items = CartItem::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($this->formatCart($items));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock'], 422);
        }

        $item = CartItem::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['quantity' => DB::raw('quantity + ' . $request->quantity), 'price' => $product->current_price]
        );

        return response()->json(['message' => 'Added to cart', 'item' => $item->load('product')]);
    }

    public function update(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = CartItem::where('user_id', $request->user()->id)->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'item' => $item->load('product')]);
    }

    public function remove(Request $request, $itemId)
    {
        CartItem::where('user_id', $request->user()->id)->findOrFail($itemId)->delete();
        return response()->json(['message' => 'Item removed']);
    }

    public function clear(Request $request)
    {
        CartItem::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Cart cleared']);
    }

    public function mergeGuestCart(Request $request)
    {
        $request->validate(['session_id' => 'required|string']);

        $guestItems = CartItem::with('product')
            ->where('session_id', $request->session_id)
            ->get();

        foreach ($guestItems as $guestItem) {
            CartItem::updateOrCreate(
                ['user_id' => $request->user()->id, 'product_id' => $guestItem->product_id],
                ['quantity' => DB::raw('COALESCE(quantity, 0) + ' . $guestItem->quantity), 'price' => $guestItem->price]
            );
        }

        CartItem::where('session_id', $request->session_id)->delete();

        return response()->json(['message' => 'Cart merged successfully']);
    }

    // Guest cart
    public function guestIndex($sessionId)
    {
        $items = CartItem::with('product')->where('session_id', $sessionId)->get();
        return response()->json($this->formatCart($items));
    }

    public function guestAdd(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock'], 422);
        }

        $existing = CartItem::where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $request->quantity]);
            $item = $existing;
        } else {
            $item = CartItem::create([
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->current_price,
            ]);
        }

        return response()->json(['message' => 'Added to cart', 'item' => $item->load('product')]);
    }

    public function guestUpdate(Request $request, $sessionId, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $item = CartItem::where('session_id', $sessionId)->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Cart updated', 'item' => $item->load('product')]);
    }

    public function guestRemove($sessionId, $itemId)
    {
        CartItem::where('session_id', $sessionId)->findOrFail($itemId)->delete();
        return response()->json(['message' => 'Item removed']);
    }

    public function guestClear($sessionId)
    {
        CartItem::where('session_id', $sessionId)->delete();
        return response()->json(['message' => 'Cart cleared']);
    }

    private function formatCart($items)
    {
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $delivery_fee = $subtotal >= 50 ? 0 : 4.99;
        $tax = $subtotal * 0.08;
        $total = $subtotal + $delivery_fee + $tax;

        return [
            'items' => $items,
            'summary' => [
                'subtotal' => round($subtotal, 2),
                'delivery_fee' => round($delivery_fee, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2),
                'free_delivery_threshold' => 50,
                'remaining_for_free_delivery' => max(0, 50 - $subtotal),
            ],
        ];
    }
}
