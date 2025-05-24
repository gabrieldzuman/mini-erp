@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Produtos</h2>
    <a href="{{ route('products.create') }}" class="btn btn-success mb-3">+ Novo Produto</a>

    @foreach($products as $product)
        <div class="card mb-3">
            <div class="card-body">
                <h4>{{ $product->name }} - R$ {{ number_format($product->price, 2, ',', '.') }}</h4>
                <ul>
                    @foreach($product->variations as $var)
                        <li>
                            <strong>{{ $var->variation }}</strong> - Estoque: {{ $var->stock->quantity ?? 0 }}
                            <form action="{{ route('cart.add', $var->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="number" name="quantity" min="1" value="1" class="form-control d-inline w-25">
                                <button type="submit" class="btn btn-primary btn-sm">Adicionar ao Carrinho</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">Editar</a>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
