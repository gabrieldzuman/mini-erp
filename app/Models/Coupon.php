<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'name',
        'min_purchase',
        'valid_until',
        'max_usage',
        'times_used',
        'discount_value',
        'discount_type',
    ];

    protected $dates = ['valid_until'];
}

