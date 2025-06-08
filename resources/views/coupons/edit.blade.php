@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Cupom</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Cupom:</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $coupon->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="min_purchase" class="form-label">Valor Mínimo da Compra (R$):</label>
            <input type="number" name="min_purchase" class="form-control" step="0.01" value="{{ old('min_purchase', $coupon->min_purchase) }}" required>
        </div>

        <div class="mb-3">
            <label for="valid_until" class="form-label">Data de Validade (opcional):</label>
            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $coupon->valid_until) }}">
        </div>

        <div class="mb-3">
            <label for="max_usage" class="form-label">Limite de Uso (opcional):</label>
            <input type="number" name="max_usage" class="form-control" value="{{ old('max_usage', $coupon->max_usage) }}">
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection