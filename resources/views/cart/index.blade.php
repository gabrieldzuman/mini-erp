@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Carrinho</h2>

    @if(session('cart') && count(session('cart')) > 0)
       <table class="table">
            <thead>
                <tr>
                    <th>Produto / Variação</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach(session('cart') as $id => $item)
                    @php $subtotal += $item['price'] * $item['quantity']; @endphp
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>
                            {{ $item['quantity'] }}
                            <form action="{{ route('cart.decrease', $id) }}" method="POST" style="display:inline-block">
                              @csrf
                              <button type="submit" class="btn btn-warning btn-sm" title="Remover 1 unidade">-1</button>
                          </form>

                          <form action="{{ route('cart.increase', $id) }}" method="POST" style="display:inline-block">
                              @csrf
                              <button type="submit" class="btn btn-success btn-sm" title="Adicionar 1 unidade">+1</button>
                          </form>
                        </td>
                        <td>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>

    @php
        $frete = 20;
        if ($subtotal >= 52 && $subtotal <= 199.99) {
            $frete = 15;
        } elseif ($subtotal > 200) {
            $frete = 0;
        } 

        $totalSemDesconto = $subtotal + $frete;
    @endphp

    <h5>Subtotal: R$ <span id="subtotal">{{ number_format($subtotal, 2, ',', '.') }}</span></h5>
    <h5>Frete: R$ <span id="frete">{{ number_format($frete, 2, ',', '.') }}</span></h5>
    <h4>Total: R$ <span id="total">{{ number_format($totalSemDesconto, 2, ',', '.') }}</span></h4>
    <h5 id="descontoLine" style="display:none; color: green;">Desconto Cupom: -R$ <span id="descontoValor">2,99</span></h5>

    <form method="POST" action="{{ route('cart.checkout') }}" id="checkoutForm">
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
            <input type="text" name="coupon" id="coupon" placeholder="Cupom (opcional)" class="form-control">
        </div>

        <div class="mb-2">
            <input type="email" name="email" placeholder="E-mail para envio" class="form-control" required>
        </div>

        <div class="mb-2">
            <input type="text" name="customer_name" placeholder="Nome do Cliente" class="form-control" required>
        </div>

        <div class="mb-2" style="display: flex; gap: 10px; align-items: center;">
            <button type="submit" class="btn btn-success">Finalizar Pedido</button>
            
            
        </div>
    </form>
    <form action="{{ route('cart.clear') }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja remover todos os itens do carrinho?')">
            Remover Todos
        </button>
    </form>

    <script>
        function formatBRL(value) {
            return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const subtotal = parseFloat("{{ $subtotal }}");
        const frete = parseFloat("{{ $frete }}");
        const descontoFixo = 2.99;

        const couponInput = document.getElementById('coupon');
        const descontoLine = document.getElementById('descontoLine');
        const totalSpan = document.getElementById('total');

        couponInput.addEventListener('input', function() {
            const cupom = this.value.trim().toLowerCase();

            let total = subtotal + frete;
            if (cupom === 'montink') {
                total -= descontoFixo;
                if (total < 0) total = 0;
                descontoLine.style.display = 'block';
            } else {
                descontoLine.style.display = 'none';
            }

            totalSpan.textContent = formatBRL(total);
        });

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
