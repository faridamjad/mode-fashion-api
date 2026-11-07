<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   
public function index(Request $request)
{
    $limit = min((int) $request->query('limit', 10), 100);

    $products = Product::with('category')
        ->when($request->filled('category_id'), function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
        })
        ->when($request->filled('keyword'), function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        })
        ->paginate($limit);

    return ProductResource::collection($products);
}


    public function show(Product $product)
    {
        $product->load('category');

        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'img_url' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return new ProductResource($product);
    }
public function destroy(Product $product)
{
    $product->delete();

    return response()->json([
        'message' => 'Product deleted successfully'
    ]);
}

public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'sometimes|required|numeric|min:0',
        'stock' => 'sometimes|required|integer|min:0',
        'category_id' => 'nullable|exists:categories,id',
        'img_url' => 'nullable|string',
    ]);

    $product->update($validated);

    return new ProductResource($product->load('category'));
}

}
