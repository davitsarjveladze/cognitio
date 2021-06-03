<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory;
    protected $table = 'wallet';
    protected $primaryKey = 'wallet_id';
    protected $fillable = [
        'wallet_id',
        'user_id',
        'name',
        'currency_id',
        'currency_code',
        'status_id',
        'updated_at'
    ];

    public static function IsWallet($user_id,$currency_id) {
       return DB::table('wallet')
           ->where(['user_id'=>$user_id,'currency_id' => $currency_id])
           ->exists();
    }

    public static function getWalletWithCurrency($statements) {
        $query = DB::table('wallet')->where('wallet.user_id','=',$statements['user_id']);
        if (isset($statements['wallet_id']))
            $query->where('wallet_id','=',$statements['wallet_id']);
        if (isset($statements['currency_code']))
            $query->where('currency_code','=',$statements['currency_code']);
        return $query->get();
    }
}
