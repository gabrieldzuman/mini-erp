<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Product;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio!');
        }

        // Calcula subtotal
        $subtotal = collect($cart)->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        // Frete e desconto
        $freight = $subtotal >= 100 ? 0 : 15.00;
        $discount = 0;

        if ($couponId = session()->get('coupon')) {
            $coupon = Coupon::find($couponId);
            if ($coupon) {
                $discount = $coupon->discount_amount ?? 0;
            }
        }

        $total = max(0, $subtotal + $freight - $discount);
        $userId = auth()->check() ? auth()->id() : null;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $request->input('customer_name'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'subtotal' => $subtotal,
                'freight' => $freight,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pendente',
            ]);

            // Cria os itens do pedido
            foreach ($cart as $productId => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variation_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Atualiza o cupom, se existir
            if (!empty($coupon)) {
                $coupon->increment('usage_count');
            }

            DB::commit();

            // Limpa sessão do carrinho e cupom
            session()->forget('cart');
            session()->forget('coupon');

            return redirect()->route('cart.index')->with('success', 'Pedido realizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar pedido: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Ocorreu um erro ao processar seu pedido.');
        }
    }
}
