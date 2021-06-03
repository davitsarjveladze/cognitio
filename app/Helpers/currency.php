<?php
namespace App\Helpers;


use phpDocumentor\Reflection\Types\Self_;

class currency {
    static string $apikey = 'SIP1XB631NF7A9CQ';

    /*
     * ფორექსის მიხედვით კურსის განსაზღვრის ფუნქცია
     * $from_currency (მაგ. USD) ვალუტა რომელიც უნდა გადაკონვერტირდეს $to_currency(მაგ. JPY) მიმართ
     * დაბრუნებული დატა
     * "Realtime Currency Exchange Rate": {
        "1. From_Currency Code": "USD",
        "2. From_Currency Name": "United States Dollar",
        "3. To_Currency Code": "JPY",
        "4. To_Currency Name": "Japanese Yen",
        "5. Exchange Rate": "109.56000000",
        "6. Last Refreshed": "2021-06-02 20:29:01",
        "7. Time Zone": "UTC",
        "8. Bid Price": "109.55400000",
        "9. Ask Price": "109.56400000"
    }
    ინახება ინტეგერად თეთრებში (100 ზე გამრავლებული) რო მერე  ზუსტად ხდება გამრავლება
     */

    public static function ForexExchangeRates($from_currency='',$to_currency='') {
        $url = 'https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency='.$from_currency.'&to_currency='.$to_currency.'&apikey=';

        $data = self::SendRequest($url);
        $data = json_decode($data,true);
        if($data['Realtime Currency Exchange Rate'])
            return [
                'from_currency' => $from_currency,
                'to_currency' => $to_currency,
                'sell' => round($data['Realtime Currency Exchange Rate']['8. Bid Price']* 100),
                'buy' => round($data['Realtime Currency Exchange Rate']['9. Ask Price'] * 100),
            ];
        return [];
    }

    static  function SendRequest($url) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url .self::$apikey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
