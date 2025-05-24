<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = $this->calculateSubtotal($cart);
        $frete = $this->calculateFrete($subtotal);
        $total = $subtotal + $frete;

        return view('cart.index', compact('cart', 'subtotal', 'frete', 'total'));
    }

    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += 1;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Produto adicionado ao carrinho!');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);
        return redirect()->route('cart.index')->with('success', 'Produto removido!');
    }

    private function calculateSubtotal($cart)
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    private function calculateFrete($subtotal)
    {
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15.00;
        } elseif ($subtotal > 200) {
            return 0.00;
        }
        return 20.00;
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio!');
        }

        $subtotal = $this->calculateSubtotal($cart);
        $frete = $this->calculateFrete($subtotal);
        $total = $subtotal + $frete;

        // Simples armazenamento de pedido
        $order = Order::create([
            'total' => $total,
            'frete' => $frete,
            'cep' => $request->cep,
            'endereco' => $request->endereco,
            'status' => 'pendente'
        ]);

        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        session()->forget('cart');

        return redirect()->route('cart.index')->with('success', 'Pedido realizado com sucesso!');
    }
}
