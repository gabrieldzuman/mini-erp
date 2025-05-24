@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Produto</h2>
    <form method="POST" action="{{ route('products.update', $product->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Preço</label>
            <input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required>
        </div>

        <hr>
        <h5>Variações</h5>
        @foreach($product->variations as $index => $var)
            <input type="hidden" name="variations[]" value="{{ $var->id }}">
            <div class="row mb-2">
                <div class="col">
                    <input type="text" name="variation_names[]" class="form-control" value="{{ $var->variation }}" required>
                </div>
                <div class="col">
                    <input type="number" name="quantities[]" class="form-control" value="{{ $var->stock->quantity ?? 0 }}" required>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>
@endsection
