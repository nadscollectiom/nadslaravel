<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::with('category')->paginate(10);
            $categories = Category::all();
            return view('admin.admin', compact('products', 'categories'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load products: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $categories = Category::all();
            return view('admin.products.create', compact('categories'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load create page: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'sizes' => 'nullable|array',
                'sizes.*' => 'string|max:50', // Each size should be a string
            ]);

            $data = $request->only(['title', 'category_id', 'price', 'stock']);

            // Handle sizes - convert to JSON or set as null
            if ($request->has('sizes') && is_array($request->sizes)) {
                // Filter out empty values and ensure we have valid sizes
                $sizes = array_filter($request->sizes, function($size) {
                    return !empty(trim($size));
                });
                $data['sizes'] = !empty($sizes) ? array_values($sizes) : null;
            } else {
                $data['sizes'] = null;
            }

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            Product::create($data);

            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create product: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Product $product)
    {
        try {
            $categories = Category::all();
            return view('admin.products.edit', compact('product', 'categories'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load edit page: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'sizes' => 'nullable|array',
                'sizes.*' => 'string|max:50',
            ]);

            $data = $request->only(['title', 'category_id', 'price', 'stock']);

            // Handle sizes - convert to JSON or set as null
            if ($request->has('sizes') && is_array($request->sizes)) {
                // Filter out empty values and ensure we have valid sizes
                $sizes = array_filter($request->sizes, function($size) {
                    return !empty(trim($size));
                });
                $data['sizes'] = !empty($sizes) ? array_values($sizes) : null;
            } else {
                $data['sizes'] = null;
            }

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update product: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete product: ' . $e->getMessage());
        }
    }

    public function apiIndex()
    {
        try {
            $products = Product::with('category')->paginate(10);
            $categories = Category::all();

            return response()->json([
                'products' => $products,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load product data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function apiShow($id)
    {
        try {
            $product = Product::with('category')->findOrFail($id);

            return response()->json([
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}