<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('guest_email', 'like', '%' . $request->search . '%')
                  ->orWhere('guest_name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product.category'])->findOrFail($id);
        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Restore stock if cancelling
        if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }

        // Mark delivered_at timestamp
        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);

        return response()->json(['message' => 'Payment status updated', 'order' => $order]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,cancelled',
        ]);

        Order::whereIn('id', $request->order_ids)->update(['status' => $request->status]);

        return response()->json(['message' => count($request->order_ids) . ' orders updated']);
    }

    public function export(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        $orders = $query->latest()->get();

        $csv = "Order Number,Customer,Email,Status,Payment Method,Payment Status,Subtotal,Delivery Fee,Tax,Total,Date\n";

        foreach ($orders as $order) {
            $csv .= implode(',', [
                $order->order_number,
                '"' . ($order->user?->name ?? $order->guest_name) . '"',
                $order->user?->email ?? $order->guest_email,
                $order->status,
                $order->payment_method,
                $order->payment_status,
                $order->subtotal,
                $order->delivery_fee,
                $order->tax,
                $order->total,
                $order->created_at->format('Y-m-d H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->items()->delete();
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }
}
