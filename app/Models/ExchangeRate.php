<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExchangeRate extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'exchange_rate';
    protected $primaryKey = 'id';
    protected $fillable = [
        'from_currency',
        'to_currency',
        'buy',
        'sell',
    ];
}
