<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name', 'email', 'cep', 'address',
        'subtotal', 'freight', 'total', 'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
