<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Stock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('variations.stock')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'variations' => 'required|array',
            'quantities' => 'required|array',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        foreach ($request->variations as $key => $variationName) {
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'variation' => $variationName,
            ]);

            Stock::create([
                'product_variation_id' => $variation->id,
                'quantity' => $request->quantities[$key] ?? 0,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Produto criado com sucesso!');
    }

    public function edit($id)
    {
        $product = Product::with('variations.stock')->findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'variations' => 'required|array',
            'quantities' => 'required|array',
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        foreach ($request->variations as $key => $variationId) {
            $variation = ProductVariation::findOrFail($variationId);
            $variation->variation = $request->variation_names[$key];
            $variation->save();

            $stock = Stock::where('product_variation_id', $variation->id)->first();
            $stock->quantity = $request->quantities[$key] ?? 0;
            $stock->save();
        }

        return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->variations as $variation) {
            $variation->stock()->delete();
            $variation->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produto deletado com sucesso!');
    }
}
