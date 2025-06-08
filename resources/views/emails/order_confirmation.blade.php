<h2>Obrigado pelo seu pedido!</h2>

<p><strong>Endereço:</strong> {{ $order['address'] }}</p>

<h4>Itens do Pedido:</h4>
<ul>
    @foreach ($order['items'] as $item)
        <li>{{ $item['name'] }} x {{ $item['quantity'] }} - R$ {{ number_format($item['price'], 2, ',', '.') }}</li>
    @endforeach
</ul>

<p><strong>Subtotal:</strong> R$ {{ number_format($order['subtotal'], 2, ',', '.') }}</p>
<p><strong>Frete:</strong> R$ {{ number_format($order['freight'], 2, ',', '.') }}</p>
<p><strong>Desconto:</strong> R$ {{ number_format($order['desconto'], 2, ',', '.') }}</p>
<p><strong>Total:</strong> R$ {{ number_format($order['total'], 2, ',', '.') }}</p>
