@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastro de Cupom</h2>

    <form action="{{ route('coupons.store') }}" method="POST" class="mb-4">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Cupom</label>
            <input type="text" name="name" id="name" class="form-control" required placeholder="EX: DESCONTO10">
        </div>

        <div class="mb-3">
            <label for="min_purchase" class="form-label">Valor Mínimo da Compra (R$)</label>
            <input type="number" step="0.01" name="min_purchase" id="min_purchase" class="form-control" required placeholder="Ex: 100.00">
        </div>

        <div class="mb-3">
            <label for="discount_value" class="form-label">Valor do Desconto:</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="{{ old('discount_value', $coupon->discount_value ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="discount_type" class="form-label">Tipo de Desconto:</label>
            <select name="discount_type" class="form-select" required>
                <option value="fixed" {{ (old('discount_type', $coupon->discount_type ?? '') == 'fixed') ? 'selected' : '' }}>Valor Fixo (R$)</option>
                <option value="percent" {{ (old('discount_type', $coupon->discount_type ?? '') == 'percent') ? 'selected' : '' }}>Percentual (%)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="valid_until" class="form-label">Data de Validade</label>
            <input type="date" name="valid_until" id="valid_until" class="form-control">
            <div class="form-text">Preencha a data de validade OU a quantidade máxima de usos.</div>
        </div>

        <div class="mb-3">
            <label for="max_usage" class="form-label">Quantidade Máxima de Usos</label>
            <input type="number" name="max_usage" id="max_usage" class="form-control" placeholder="Ex: 100">
        </div>

        <button type="submit" class="btn btn-success">Salvar Cupom</button>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
