<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        return view('coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_purchase' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date',
            'max_usage' => 'nullable|integer|min:1',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:fixed,percent',
        ]);

        Coupon::create($request->all());

        return redirect()->route('coupons.index')->with('success', 'Cupom cadastrado com sucesso!');
    }

    public function edit(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('coupons.edit', compact('coupon'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_purchase' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date',
            'max_usage' => 'nullable|integer|min:1',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:fixed,percent',
        ]);

        $coupon = Coupon::findOrFail($id);
        $coupon->update($request->all());

        return redirect()->route('coupons.index')->with('success', 'Cupom atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('coupons.index')->with('success', 'Cupom excluído com sucesso!');
    }

    /**
     * Valida o cupom enviado via Ajax e retorna JSON com o desconto, se for válido.
     */
    public function validateCoupon(Request $request)
    {
        $code = $request->query('code');
        $subtotal = floatval($request->query('subtotal'));

        $coupon = Coupon::where('name', $code)->first();

        if (!$coupon) {
            return response()->json(['valido' => false]);
        }

        // Verifica validade por data, se aplicável
        if ($coupon->valid_until && Carbon::parse($coupon->valid_until)->isPast()) {
            return response()->json(['valido' => false]);
        }

        // Verifica se atingiu o limite de uso, se houver
        if ($coupon->max_usage && $coupon->used_count >= $coupon->max_usage) {
            return response()->json(['valido' => false]);
        }

        // Verifica valor mínimo de compra
        if ($subtotal < $coupon->min_purchase) {
            return response()->json(['valido' => false]);
        }

        // Calcula o desconto
        $desconto = 0;
        if ($coupon->discount_type === 'fixed') {
            $desconto = $coupon->discount_value;
        } elseif ($coupon->discount_type === 'percent') {
            $desconto = ($coupon->discount_value / 100) * $subtotal;
        }

        return response()->json([
            'valido' => true,
            'desconto' => number_format($desconto, 2, '.', ''),
        ]);
    }
}
