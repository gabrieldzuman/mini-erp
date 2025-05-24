@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Carrinho</h2>

    @if(session('cart') && count(session('cart')) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Variação</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach(session('cart') as $item)
                    @php $subtotal += $item['price'] * $item['quantity']; @endphp
                    <tr>
                        <td>{{ $item['product'] }}</td>
                        <td>{{ $item['variation'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</td>
                    </tr>
                    <h5>Subtotal: R$ {{ number_format($subtotal, 2, ',', '.') }}</h5>
                    <h5>Frete: R$ {{ number_format($frete, 2, ',', '.') }}</h5>
                    <h4>Total: R$ {{ number_format($total, 2, ',', '.') }}</h4>
                @endforeach
            </tbody>
        </table>

        @php
            $frete = 0;
            if ($subtotal >= 52 && $subtotal <= 166.59) {
                $frete = 15;
            } elseif ($subtotal > 200) {
                $frete = 0;
            } else {
                $frete = 20;
            }
            $total = $subtotal + $frete;
        @endphp

        <p>Subtotal: R$ {{ number_format($subtotal, 2, ',', '.') }}</p>
        <p>Frete: R$ {{ number_format($frete, 2, ',', '.') }}</p>
        <p><strong>Total: R$ {{ number_format($total, 2, ',', '.') }}</strong></p>

        <hr>
        <h5>Endereço</h5>
        <form method="POST" action="{{ route('cart.checkout') }}">
            @csrf
            <div class="row mb-2">
                <div class="col">
                    <input type="text" name="cep" id="cep" placeholder="CEP" class="form-control" required>
                </div>
                <div class="col">
                    <input type="text" name="address" id="address" placeholder="Endereço" class="form-control" required>
                </div>
            </div>

            <div class="mb-2">
                <input type="text" name="coupon" placeholder="Cupom (opcional)" class="form-control">
            </div>

            <div class="mb-2">
                <input type="text" name="email" placeholder="E-mail para envio" class="form-control" required>
            </div>

            <h4>Finalizar Pedido</h4>
            @csrf
            <div class="mb-2">
                <label for="email" class="form-label">Seu e-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-2">
                <label for="address" class="form-label">Endereço</label>
                <input type="text" name="address" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Finalizar Pedido</button>
        </form>

        <script>
            document.getElementById('cep').addEventListener('blur', async function () {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await res.json();
                    if (!data.erro) {
                        document.getElementById('address').value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                    }
                }
            });
        </script>
    @else
        <p>Seu carrinho está vazio.</p>
    @endif
</div>
@endsection
