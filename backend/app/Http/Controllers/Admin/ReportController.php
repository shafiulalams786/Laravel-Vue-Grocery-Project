<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $orders = Order::whereBetween('created_at', [$request->from, Carbon::parse($request->to)->endOfDay()])
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue, SUM(subtotal) as subtotal, SUM(tax) as tax, SUM(delivery_fee) as delivery_fees')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_revenue'    => $orders->sum('revenue'),
            'total_orders'     => $orders->sum('orders'),
            'total_tax'        => $orders->sum('tax'),
            'total_delivery'   => $orders->sum('delivery_fees'),
            'avg_order_value'  => $orders->count() ? round($orders->sum('revenue') / $orders->sum('orders'), 2) : 0,
        ];

        return response()->json(compact('orders', 'summary'));
    }

    public function products(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $items = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$request->from, Carbon::parse($request->to)->endOfDay()])
            ->where('orders.payment_status', 'paid')
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as units_sold, SUM(order_items.total) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->paginate(20);

        return response()->json($items);
    }

    public function customers(Request $request)
    {
        $newCustomers = User::where('is_guest', false)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topCustomers = User::where('is_guest', false)
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total')
            ->orderByDesc('orders_sum_total')
            ->take(10)
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json(compact('newCustomers', 'topCustomers'));
    }

    public function exportSalesCsv(Request $request)
    {
        $orders = Order::with('items')
            ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->where('payment_status', 'paid')
            ->latest()
            ->get();

        $csv = "Order Number,Customer,Email,Payment Method,Items,Subtotal,Delivery,Tax,Total,Date\n";

        foreach ($orders as $order) {
            $csv .= implode(',', [
                $order->order_number,
                '"' . ($order->user?->name ?? $order->guest_name) . '"',
                $order->user?->email ?? $order->guest_email,
                $order->payment_method,
                $order->items->count(),
                $order->subtotal,
                $order->delivery_fee,
                $order->tax,
                $order->total,
                $order->created_at->format('Y-m-d H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
