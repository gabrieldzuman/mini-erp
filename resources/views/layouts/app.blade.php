<!DOCTYPE html>
<html>
<head>
    <title>Mini ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/products">Montink ERP</a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('products.index') }}">Produtos</a>
                <a class="nav-link" href="{{ route('cart.index') }}">Carrinho</a>
                <a class="nav-link" href="{{ route('coupons.index') }}">Cupons</a> <!-- Adicionamos aqui o botão de cupons -->
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>
</body>
</html>
