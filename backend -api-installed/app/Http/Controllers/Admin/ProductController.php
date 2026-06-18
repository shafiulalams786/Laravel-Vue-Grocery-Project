<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->stock === 'low') {
            $query->where('stock', '>', 0)->where('stock', '<=', 10);
        } elseif ($request->stock === 'out') {
            $query->where('stock', 0);
        }

        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        $query->orderBy($sort, $dir);

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function show($id)
    {
        return response()->json(Product::with('category')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'unit'        => 'required|string',
            // Accept "true"/"false"/"1"/"0"/true/false (multipart sends strings)
            'is_featured' => 'nullable|in:0,1,true,false',
            'is_active'   => 'nullable|in:0,1,true,false',
            'origin'      => 'nullable|string',
            'weight'      => 'nullable|numeric',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data         = $request->except('image');
        $data['slug'] = $this->uniqueSlug($request->name);

        // Cast string booleans coming from multipart/form-data
        $data['is_featured'] = filter_var($request->input('is_featured', false), FILTER_VALIDATE_BOOLEAN);
        $data['is_active']   = filter_var($request->input('is_active', true),  FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created',
            'product' => $product->load('category'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'unit'        => 'sometimes|string',
            'is_featured' => 'nullable|in:0,1,true,false',
            'is_active'   => 'nullable|in:0,1,true,false',
            'origin'      => 'nullable|string',
            'weight'      => 'nullable|numeric',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->has('name') && $request->name !== $product->name) {
            $data['slug'] = $this->uniqueSlug($request->name, $product->id);
        }

        // Cast string booleans
        if ($request->has('is_featured')) {
            $data['is_featured'] = filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated',
            'product' => $product->fresh('category'),
        ]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'action' => 'required|in:activate,deactivate,delete,feature,unfeature',
        ]);

        $products = Product::whereIn('id', $request->ids);

        match ($request->action) {
            'activate'   => $products->update(['is_active' => true]),
            'deactivate' => $products->update(['is_active' => false]),
            'feature'    => $products->update(['is_featured' => true]),
            'unfeature'  => $products->update(['is_featured' => false]),
            'delete'     => $products->each->delete(),
        };

        return response()->json(['message' => 'Bulk action applied to ' . count($request->ids) . ' products']);
    }

    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'stock'  => 'required|integer|min:0',
            'reason' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);
        $product->update(['stock' => $request->stock]);

        return response()->json(['message' => 'Stock updated', 'product' => $product]);
    }

    private function uniqueSlug(string $name, $excludeId = null): string
    {
        $slug     = Str::slug($name);
        $original = $slug;
        $i        = 1;

        while (
            Product::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}
