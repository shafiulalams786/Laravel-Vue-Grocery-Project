<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|in:0,1,true,false',
            'sort_order'  => 'nullable|integer',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data         = $request->except('image');
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json(['message' => 'Category created', 'category' => $category], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'        => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'nullable|in:0,1,true,false',
            'sort_order'  => 'nullable|integer',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return response()->json(['message' => 'Category updated', 'category' => $category->fresh()]);
    }

    public function destroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return response()->json([
                'message' => 'Cannot delete category with products. Move or delete products first.'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Category order saved']);
    }
}
