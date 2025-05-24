<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class WebhookController extends Controller
{
    public function receber(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|integer',
            'status' => 'required|string',
        ]);

        $pedido = Order::find($validated['pedido_id']);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        if (strtolower($validated['status']) === 'cancelado') {
            $pedido->delete();
            return response()->json(['message' => 'Pedido cancelado e removido com sucesso']);
        } else {
            $pedido->status = $validated['status'];
            $pedido->save();
            return response()->json(['message' => 'Status do pedido atualizado para ' . $validated['status']]);
        }
    }
}
