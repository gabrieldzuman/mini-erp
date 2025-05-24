@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Produto</h2>
    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        <div class="form-group mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Preço</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <hr>
        <h5>Variações</h5>
        <div id="variations-container"></div>
        <button type="button" class="btn btn-secondary my-2" onclick="addVariation()">+ Variação</button>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>

<script>
    let variationIndex = 0;

    function addVariation() {
        const container = document.getElementById('variations-container');
        container.insertAdjacentHTML('beforeend', `
            <div class="row mb-2">
                <div class="col">
                    <input type="text" name="variations[]" placeholder="Nome da variação" class="form-control" required>
                </div>
                <div class="col">
                    <input type="number" name="quantities[]" placeholder="Estoque" class="form-control" required>
                </div>
            </div>
        `);
        variationIndex++;
    }
</script>
@endsection
