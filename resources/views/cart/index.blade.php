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
                            <form action="{{ route('cart.decrease', $id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" title="Remover 1 unidade">-1</button>
                            </form>

                            <form action="{{ route('cart.increase', $id) }}" method="POST" style="display:inline-block;">
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
            $freight = 20;
            if ($subtotal >= 52 && $subtotal <= 199.99) {
                $freight = 15;
            } elseif ($subtotal > 200) {
                $freight = 0;
            }
            $totalSemDesconto = $subtotal + $freight;
        @endphp

        <h5>Subtotal: R$ <span id="subtotal">{{ number_format($subtotal, 2, ',', '.') }}</span></h5>
        <h5>Frete: R$ <span id="freight">{{ number_format($freight, 2, ',', '.') }}</span></h5>
        <h4>Total: R$ <span id="total">{{ number_format($totalSemDesconto, 2, ',', '.') }}</span></h4>

        <h5 id="descontoLine" style="display: none; color: green;">
            Desconto Cupom: -R$ <span id="descontoValor">0,00</span>
        </h5>
        <h5 id="cupomMsg" style="display: none; color: green;">Cupom ativado com sucesso!</h5>
        <h5 id="cupomInvalidoMsg" style="display: none; color: red;">Cupom inválido.</h5>

        <form method="POST" action="{{ route('checkout') }}" id="checkoutForm">
            @csrf
            <div class="row mb-2">
                <div class="col">
                    <input type="text" name="cep" id="cep" placeholder="CEP" class="form-control">
                </div>
                <div class="col">
                    <input type="text" name="address" id="address" placeholder="Endereço" class="form-control">
                </div>
            </div>

            <div class="mb-2">
                <input type="text" name="coupon" id="coupon" placeholder="Cupom (opcional)" class="form-control">
            </div>

            <div class="mb-2">
                <input type="email" name="email" placeholder="E-mail para envio" class="form-control">
            </div>

            <div class="mb-2">
                <input type="text" name="customer_name" placeholder="Nome do Cliente" class="form-control">
            </div>

            <div class="mb-2" style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="btn btn-success">Finalizar Pedido</button>
            </div>
        </form>

        <form action="{{ route('cart.clear') }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja remover todos os itens do carrinho?')">
                Remover Todos
            </button>
        </form>

        <script>
            function formatBRL(value) {
                return value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            const subtotal = parseFloat("{{ $subtotal }}");
            const freight = parseFloat("{{ $freight }}");
            const totalSemDesconto = subtotal + freight;

            const couponInput = document.getElementById('coupon');
            const descontoLine = document.getElementById('descontoLine');
            const descontoValorSpan = document.getElementById('descontoValor');
            const totalSpan = document.getElementById('total');
            const cupomMsg = document.getElementById('cupomMsg');
            const cupomInvalidoMsg = document.getElementById('cupomInvalidoMsg');

            couponInput.addEventListener('input', function() {
                const inputCupom = this.value.trim();

                if (inputCupom.length === 0) {
                    descontoLine.style.display = 'none';
                    descontoValorSpan.textContent = '0,00';
                    totalSpan.textContent = formatBRL(totalSemDesconto);
                    cupomMsg.style.display = 'none';
                    cupomInvalidoMsg.style.display = 'none';
                    return;
                }

                fetch(`/validar-cupom`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: inputCupom })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valido) {
                        const descontoAplicado = parseFloat(data.desconto);
                        let total = totalSemDesconto - descontoAplicado;
                        if (total < 0) total = 0;

                        descontoLine.style.display = 'block';
                        descontoValorSpan.textContent = formatBRL(descontoAplicado);
                        totalSpan.textContent = formatBRL(total);
                        cupomMsg.style.display = 'block';
                        cupomInvalidoMsg.style.display = 'none';
                    } else {
                        descontoLine.style.display = 'none';
                        descontoValorSpan.textContent = '0,00';
                        totalSpan.textContent = formatBRL(totalSemDesconto);
                        cupomMsg.style.display = 'none';
                        cupomInvalidoMsg.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Erro ao validar cupom:', err);
                });
            });

            document.getElementById('cep').addEventListener('blur', async function () {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    try {
                        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                        const data = await res.json();
                        if (!data.erro) {
                            document.getElementById('address').value =
                                `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                        }
                    } catch (e) {
                        console.error('Erro ao buscar CEP:', e);
                    }
                }
            });
        </script>
    @else
        <p>Seu carrinho está vazio.</p>
    @endif
</div>
@endsection
