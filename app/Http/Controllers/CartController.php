<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\ProductVariation;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = $this->calculateSubtotal($cart);
        $freight = $this->calculateFrete($subtotal);
        $discount = $this->getDiscount($subtotal);
        $total = max(0, $subtotal + $freight - $discount);

        return view('cart.index', compact('cart', 'subtotal', 'freight', 'discount', 'total'));
    }

    public function add(Request $request, $id)
    {
        $variation = ProductVariation::findOrFail($id);
        $product = $variation->product;

        $cart = session()->get('cart', []);
        $quantityToAdd = max(1, (int) $request->input('quantity', 1));

        if (isset($cart[$variation->id])) {
            $cart[$variation->id]['quantity'] += $quantityToAdd;
        } else {
            $cart[$variation->id] = [
                'name' => $product->name . ' - ' . $variation->variation,
                'price' => $variation->price ?? $product->price,
                'quantity' => $quantityToAdd,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->route('cart.index')->with('success', 'Produto removido!');
    }

    public function decrease($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            if ($cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
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
            $cart[$id]['quantity']++;
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Quantidade do item aumentada.');
    }

    public function applyCoupon(Request $request)
    {
        $code = $request->input('coupon');
        if (!$code) {
            return redirect()->back()->with('error', 'Informe um código de cupom.');
        }

        $coupon = Coupon::where('name', $code)->first();
        if (!$coupon) {
            return redirect()->back()->with('error', 'Cupom inválido.');
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            return redirect()->back()->with('error', 'Cupom expirado.');
        }

        if ($coupon->max_usage && $coupon->usage_count >= $coupon->max_usage) {
            return redirect()->back()->with('error', 'Limite de uso do cupom atingido.');
        }

        session()->put('coupon', $coupon->id);

        return redirect()->route('cart.index')->with('success', 'Cupom aplicado com sucesso!');
    }

public function checkout(Request $request)
    {
        // Validação dos dados enviados no request
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Obtém o carrinho da sessão
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio!');
        }

        // Calcula valores do pedido
        $subtotal = $this->calculateSubtotal($cart);
        $freight = $this->calculateFrete($subtotal);
        $discount = $this->getDiscount($subtotal);
        $total = max(0, $subtotal + $freight - $discount);

        \DB::beginTransaction();

        try {
            // Cria o pedido
            $order = Order::create([
                'user_id' => auth()->id(), // Assuming user is logged in
                'customer_name' => $validated['customer_name'],
                'email' => $validated['email'],
                'subtotal' => $subtotal,
                'freight' => $freight,
                'discount' => $discount,
                'total' => $total,
                'address' => $validated['address'],
                'status' => 'pendente',
            ]);

            // Cria os itens do pedido
            foreach ($cart as $variationId => $item) {
                // Buscando a variação para garantir integridade dos dados
                $variation = ProductVariation::find($variationId);
                if (!$variation) {
                    throw new \Exception("Variação do produto com ID {$variationId} não encontrada.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variation_id' => $variation->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Atualiza o uso do cupom, se houver
            if ($couponId = session('coupon')) {
                $coupon = Coupon::find($couponId);
                if ($coupon) {
                    $coupon->increment('usage_count');
                }
            }

            \DB::commit();

            // Limpa carrinho e cupom da sessão
            session()->forget(['cart', 'coupon']);

            return redirect()->route('cart.index')->with('success', 'Pedido realizado com sucesso!');
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Erro no checkout: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'cart' => $cart,
                'request' => $request->all(),
            ]);

            return redirect()->route('cart.index')->with('error', 'Erro ao processar pedido. Tente novamente.');
        }
    }

    private function calculateSubtotal(array $cart): float
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    private function calculateFrete(float $subtotal): float
    {
        if ($subtotal >= 52 && $subtotal <= 199.99) {
            return 15.00;
        } elseif ($subtotal > 200) {
            return 0.00;
        }
        return 20.00;
    }

    private function getDiscount(float $subtotal): float
    {
        $couponId = session('coupon');
        if (!$couponId) {
            return 0;
        }

        $coupon = Coupon::find($couponId);
        if (!$coupon) {
            return 0;
        }

        if ($coupon->min_purchase > 0 && $subtotal < $coupon->min_purchase) {
            return 0;
        }

        if ($coupon->discount_type === 'fixed') {
            return min($coupon->discount_value, $subtotal);
        } elseif ($coupon->discount_type === 'percent') {
            return ($subtotal * $coupon->discount_value) / 100;
        }

        return 0;
    }

    public function clear()
    {
        session()->forget(['cart', 'coupon']);
        return redirect()->back()->with('success', 'Carrinho esvaziado com sucesso!');
    }
}
