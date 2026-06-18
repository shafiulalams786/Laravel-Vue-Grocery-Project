<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_guest', false)
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status === 'active') {
            $query->where('is_banned', false);
        } elseif ($request->status === 'banned') {
            $query->where('is_banned', true);
        }

        $customers = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($customers);
    }

    public function show($id)
    {
        $customer = User::where('is_guest', false)->findOrFail($id);
        $orders = Order::where('user_id', $id)->with('items')->latest()->take(10)->get();

        $stats = [
            'total_orders' => Order::where('user_id', $id)->count(),
            'total_spent' => Order::where('user_id', $id)->where('payment_status', 'paid')->sum('total'),
            'avg_order_value' => Order::where('user_id', $id)->where('payment_status', 'paid')->avg('total') ?? 0,
        ];

        return response()->json(compact('customer', 'orders', 'stats'));
    }

    public function toggleBan(Request $request, $id)
    {
        $customer = User::where('is_guest', false)->findOrFail($id);
        $customer->update(['is_banned' => !$customer->is_banned]);

        $action = $customer->is_banned ? 'banned' : 'unbanned';
        return response()->json(['message' => "Customer {$action}", 'customer' => $customer]);
    }

    public function export()
    {
        $customers = User::where('is_guest', false)
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total')
            ->get();

        $csv = "ID,Name,Email,Phone,Orders,Total Spent,Joined,Banned\n";

        foreach ($customers as $c) {
            $csv .= implode(',', [
                $c->id,
                '"' . $c->name . '"',
                $c->email,
                $c->phone ?? '',
                $c->orders_count,
                round($c->orders_sum_total ?? 0, 2),
                $c->created_at->format('Y-m-d'),
                $c->is_banned ? 'Yes' : 'No',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
