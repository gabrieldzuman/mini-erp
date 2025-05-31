<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;
use App\Models\ProductVariation;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = $this->calculateSubtotal($cart);
        $frete = $this->calculateFrete($subtotal);
        $discount = $this->getDiscount();
        $total = max(0, $subtotal + $frete);

        return view('cart.index', compact('cart', 'subtotal', 'frete', 'total', 'discount'));
    }

    public function add(Request $request, $id)
    {
        $variation = ProductVariation::findOrFail($id);
        $product = $variation->product;
        $cart = session()->get('cart', []);

        if (isset($cart[$variation->id])) {
            $cart[$variation->id]['quantity'] += $request->input('quantity', 1);
        } else {
            $cart[$variation->id] = [
                'name' => $product->name . ' - ' . $variation->variation,
                'price' => $variation->price ?? $product->price,
                'quantity' => $request->input('quantity', 1),
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Produto removido!');
    }

    public function decrease($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            if ($cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity'] -= 1;
            } else {
                unset($cart[$id]);
            }
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Item atualizado no carrinho.');
    }

    public function increase($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Quantidade do item aumentada.');
    }

    public function applyCoupon(Request $request)
    {
        $code = strtolower($request->input('coupon'));

        if ($code === 'montink') {
            session()->put('coupon', $code);
            return redirect()->route('cart.index')->with('success', 'Cupom aplicado com sucesso!');
        }

        return redirect()->back()->with('error', 'Cupom inválido.');
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio!');
        }

        $coupon = $request->input('coupon');
        if ($coupon && strtolower($coupon) === 'montink') {
            session(['coupon' => 'montink']);
        } else {
            session()->forget('coupon');
        }

        $subtotal = $this->calculateSubtotal($cart);
        $frete = $this->calculateFrete($subtotal);
        $discount = $this->getDiscount();
        $frete - $discount;
        $total = max(0, $subtotal + $frete - $discount);
        $total = max(0, $subtotal + $frete);

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
        session()->forget('coupon');

        return redirect()->route('cart.index')->with('success', 'Pedido realizado com sucesso!');
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
        if ($subtotal >= 52 && $subtotal <= 199.99) {
            return 15.00;
        } elseif ($subtotal > 200) {
            return 0.00;
        }
        return 20.00;
    }

    private function getDiscount()
    {
        return session('coupon') === 'montink' ? 2.99 : 0;
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->back()->with('success', 'Carrinho esvaziado com sucesso!');
    }

}
