<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class POSRate extends Model
{
    protected $table = "pos_rates";
    protected $fillable = [
        'pos_name',
        'card_type',
        'card_brand',
        'installment',
        'currency',
        'commission_rate',
        'min_fee',
        'priority'
    ];

    protected $casts = [
        'commission_rate' => 'float',
        'installment'     => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('pos_rates');
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }
}
