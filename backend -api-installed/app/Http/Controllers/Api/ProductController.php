<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->active()->inStock();

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($request->sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($request->sort === 'newest') {
            $query->latest();
        } else {
            $query->orderBy('name');
        }

        return response()->json($query->paginate(20));
    }

    public function featured()
    {
        $products = Product::with('category')->active()->featured()->inStock()->take(8)->get();
        return response()->json($products);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $products = Product::with('category')
            ->active()
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('description', 'like', '%' . $request->q . '%');
            })
            ->take(20)
            ->get();

        return response()->json($products);
    }

    public function show($slug)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();
        $related = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()->take(4)->get();

        return response()->json(compact('product', 'related'));
    }
}
