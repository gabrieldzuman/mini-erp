@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cupons</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('coupons.create') }}" class="btn btn-success mb-3">+ Novo Cupom</a>

    @if($coupons->count())
        @foreach($coupons as $coupon)
            <div class="card mb-2">
                <div class="card-body">
                    <h4>{{ $coupon->name }}</h4>
                    <p>
                        Valor mínimo de compra: R$ {{ number_format($coupon->min_purchase, 2, ',', '.') }}<br>
                        Data de validade: {{ $coupon->valid_until ? \Carbon\Carbon::parse($coupon->valid_until)->format('d/m/Y') : 'Ilimitado' }}<br>
                        Limite de uso: {{ $coupon->max_usage ?? 'Ilimitado' }}
                    </p>

                    <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                    </form>
                </div>
            </div>
        @endforeach
    @else
        <p>Nenhum cupom cadastrado ainda.</p>
    @endif
</div>
@endsection
