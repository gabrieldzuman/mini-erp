<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_value', 'min_value', 'valid_until'];

    public function isValid(): bool
    {
        return Carbon::now()->lte(Carbon::parse($this->valid_until));
    }
}
