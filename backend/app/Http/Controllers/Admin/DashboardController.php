<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Revenue stats
        $revenueToday = Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total');
        $revenueWeek = Order::where('created_at', '>=', $thisWeek)->where('payment_status', 'paid')->sum('total');
        $revenueMonth = Order::where('created_at', '>=', $thisMonth)->where('payment_status', 'paid')->sum('total');
        $revenueLastMonth = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->where('payment_status', 'paid')->sum('total');
        $revenueTotal = Order::where('payment_status', 'paid')->sum('total');

        // Order counts
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersWeek = Order::where('created_at', '>=', $thisWeek)->count();
        $ordersMonth = Order::where('created_at', '>=', $thisMonth)->count();
        $ordersTotal = Order::count();
        $ordersPending = Order::where('status', 'pending')->count();
        $ordersProcessing = Order::whereIn('status', ['confirmed', 'processing', 'out_for_delivery'])->count();

        // Customers
        $customersTotal = User::where('is_guest', false)->count();
        $customersNew = User::where('is_guest', false)->where('created_at', '>=', $thisMonth)->count();
        $guestOrders = Order::whereNull('user_id')->count();

        // Products
        $productsTotal = Product::count();
        $productsLowStock = Product::where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $productsOutOfStock = Product::where('stock', 0)->count();

        // Average order value
        $avgOrderValue = Order::where('payment_status', 'paid')->avg('total') ?? 0;

        // Revenue change %
        $revenueChange = $revenueLastMonth > 0
            ? round((($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        return response()->json([
            'revenue' => [
                'today' => round($revenueToday, 2),
                'week' => round($revenueWeek, 2),
                'month' => round($revenueMonth, 2),
                'total' => round($revenueTotal, 2),
                'change_percent' => $revenueChange,
            ],
            'orders' => [
                'today' => $ordersToday,
                'week' => $ordersWeek,
                'month' => $ordersMonth,
                'total' => $ordersTotal,
                'pending' => $ordersPending,
                'processing' => $ordersProcessing,
            ],
            'customers' => [
                'total' => $customersTotal,
                'new_this_month' => $customersNew,
                'guest_orders' => $guestOrders,
            ],
            'products' => [
                'total' => $productsTotal,
                'low_stock' => $productsLowStock,
                'out_of_stock' => $productsOutOfStock,
            ],
            'avg_order_value' => round($avgOrderValue, 2),
        ]);
    }

    public function revenueChart(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $days = (int) $period;

        $data = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing days with 0
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $found = $data->firstWhere('date', $date);
            $result[] = [
                'date' => $date,
                'revenue' => $found ? round($found->revenue, 2) : 0,
                'orders' => $found ? $found->orders : 0,
            ];
        }

        return response()->json($result);
    }

    public function ordersByStatus()
    {
        $data = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($row) => [$row->status => $row->count]);

        return response()->json($data);
    }

    public function ordersByPaymentMethod()
    {
        $data = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        return response()->json($data);
    }

    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);

        $products = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as units_sold, SUM(total) as revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->take($limit)
            ->get();

        return response()->json($products);
    }

    public function lowStockAlerts()
    {
        $products = Product::with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->get(['id', 'name', 'stock', 'category_id', 'slug']);

        return response()->json($products);
    }

    public function recentOrders()
    {
        $orders = Order::with('items')
            ->latest()
            ->take(10)
            ->get();

        return response()->json($orders);
    }

    public function categoryRevenue()
    {
        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(order_items.total) as revenue, SUM(order_items.quantity) as units_sold')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return response()->json($data);
    }
}
