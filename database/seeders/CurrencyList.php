<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Console\Commands;

class CurrencyList extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $basicCurrencyArray = [
            [
                'country' => 'UNITED STATES OF AMERICA',
                'currency_code' => 'USD'
            ],
            [
                'country' => 'GEORGIA',
                'currency_code' => 'GEL'
            ],
        ];
        DB::table('currency_list')->insert($basicCurrencyArray);


        $ExchangeRate = [];
        foreach ($basicCurrencyArray as $MainBasic) {
            foreach ($basicCurrencyArray as $SubBasic) {
                $ExchangeRate[] = [
                        'from_currency' => $MainBasic['currency_code'],
                        'to_currency' => $SubBasic['currency_code'],
                        'buy' => 0,
                        'sell' => 0,
                    ];
            }
        }

        DB::table('exchange_rate')->insert($ExchangeRate);


    }
}
