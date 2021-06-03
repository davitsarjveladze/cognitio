<?php

namespace App\Console\Commands;

use App\Helpers\currency;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;

class GetCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ვალუტის კურსის განახლება ბაზაში';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        foreach (ExchangeRate::select('from_currency','to_currency','id')->get() as $Currencies){
            if($Currencies['from_currency'] !== $Currencies['to_currency']) {
                $UpdatedCurrencies = currency::ForexExchangeRates($Currencies['from_currency'], $Currencies['to_currency']);
            } else {
                $UpdatedCurrencies = [
                    'from_currency' => $Currencies['from_currency'],
                    'to_currency' => $Currencies['to_currency'],
                    'sell' => 100,
                    'buy' => 100,
                ];
            }
            ExchangeRate::where('id','=',$Currencies['id'])
                ->update($UpdatedCurrencies);
        }
    }
}
