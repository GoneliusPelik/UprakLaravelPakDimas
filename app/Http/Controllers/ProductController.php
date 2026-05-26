<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::with('category')->get();

        return response()->json([
            'message'  => 'Daftar produk',
            'products' => $products,
        ]);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $slug = Str::slug($validated['name']);

        if (Product::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => 'Nama produk tidak bisa digunakan karena slug sudah ada',
            ], 422);
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'is_active'   => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Produk berhasil dibuat',
            'product' => $product,
        ], 201);
    }

    // GET /api/products/{id}
    public function show(Product $product)
    {
        $product->load('category');

        return response()->json([
            'message' => 'Detail produk',
            'product' => $product,
        ]);
    }

    // PUT /api/products/{id}
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $slug = Str::slug($validated['name']);

            if (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                return response()->json([
                    'message' => 'Nama produk tidak bisa digunakan karena slug sudah ada',
                ], 422);
            }

            $validated['slug'] = $slug;
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'product' => $product,
        ]);
    }

    // DELETE /api/products/{id}
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus permanen',
        ]);
    }
}
