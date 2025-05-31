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

    public static function createFixedMontinkCoupon(): self
    {
        return self::firstOrCreate(
['code' => 'montink'],
    ['discount_value' => 2.99, 'min_value' => 0, 'valid_until' => now()->addYears(1)]
        );
    }

}
