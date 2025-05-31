<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WebhookController;

Route::resource('products', ProductController::class);
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{variationId}', [CartController::class, 'add'])->name('cart.add'); // Apenas uma rota 
Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/cart/finalizar', [CartController::class, 'finalizarPedido'])->name('cart.finalizar');
Route::post('/webhook/pedido', [WebhookController::class, 'receber']);
Route::post('/cart/decrease/{variationId}', [CartController::class, 'decrease'])->name('cart.decrease');
Route::post('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
