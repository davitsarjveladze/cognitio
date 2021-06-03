<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use http\Env\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\ExchangeRate;

class TransactionController extends Controller
{
    //
    public function exchangeByWalletId(Request $request): \Illuminate\Http\JsonResponse
    {
        // prepare data for transaction
        $data = self::prepareTransactionData(2,$request->all());

        $SenderWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['sender_wallet_id']]);
        if (count($SenderWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'not found']);
        }

        $ReceiverWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['receiver_wallet_id']]);
        if (count($ReceiverWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'ანგარიში არ არსებობს']);
        }

        return self::makeTransaction($data,$ReceiverWallet,$SenderWallet,0);

    }
    public function transactionBetweenToUsers(Request $request)
    {
        // prepare data for transaction
        $data = self::prepareTransactionData(1,$request->all());

        $SenderWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['sender_wallet_id']]);
        if (count($SenderWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'not found']);
        }

        $ReceiverWallet = Wallet::getWalletWithCurrency(['user_id' => $data['receiver_id'], 'wallet_id' => $data['receiver_wallet_id']]);
        if (count($ReceiverWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'ანგარიში არ არსებობს']);
        }

        if($SenderWallet[0]->currency_code !== $ReceiverWallet[0]->currency_code) {
            return response()->json(['status' => 0, 'error' => 'ვალუტა არ ემთხვევა']);
        }

        return self::makeTransaction($data,$ReceiverWallet,$SenderWallet,1);

    }


    private static function prepareTransactionData($type_id, $request)
    {
        $rulers = [
            'sender_wallet_id' => 'required|integer',
            'receiver_wallet_id' => 'required|integer',
            'value' => 'required|numeric',
        ];
        print_r($rulers);
        if ($type_id === 1) {
            $rulers['receiver_id'] = 'required|integer';
        }
        $data = Validator::make($request,$rulers)->validate();
        $data['sender_id'] = Auth::id();
        if ($type_id === 2)
            $data['receiver_id'] = Auth::id();
        $data['type_id'] = $type_id;
        $data['status_id'] = 1;
        $data['value'] = $data['value'] * 100;

        return $data;
    }

    private static function makeTransaction($data,$ReceiverWallet,$SenderWallet,$Percent): \Illuminate\Http\JsonResponse
    {
        $percentPlus = $data['value'] * $Percent/100;
        if ($SenderWallet[0]->balance >= $data['value'] + $percentPlus) {
            $newSenderBalance = $SenderWallet[0]->balance - $data['value'] - $percentPlus;
            $exchangeRate = self::getExchangeRate($SenderWallet[0]->currency_code,$ReceiverWallet[0]->currency_code);
            $newReceiverBalance = $ReceiverWallet[0]->balance + ($data['value']/($exchangeRate[0]->buy/100));

            if(!self::updateWallet($SenderWallet[0]->wallet_id,['balance' => $newSenderBalance])) {
                return response()->json(['status' => 0, 'error' => 'ბლანსი ვერ განახლდა']);
            }
            if (!self::updateWallet($ReceiverWallet[0]->wallet_id,['balance' => $newReceiverBalance])){
                return response()->json(['status' => 0, 'error' => 'ბლანსი ვერ განახლდა']);
            }
            $data['currency_code'] = $SenderWallet[0]->currency_code;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            if (!self::createTransaction($data)) {
                return response()->json(['status' => 0, 'error' => 'ტრანაზაქცია ვერ შეინახა']);
            }
        } else {
            return response()->json(['status' => 0, 'error' => 'ანგარიშზე არასაკმარისი თანხაა']);
        }
        return  response()->json(['status' => 1, 'data' => 'success']);
    }


    private static function getExchangeRate($from_currency,$to_currency) {
        return ExchangeRate::where('from_currency','=',$from_currency)
            ->where('to_currency','=',$to_currency)->get();
    }

    private static function updateWallet($id,$updateData) {
        return Wallet::where('wallet_id','=',$id)->update($updateData);
    }

    private static function createTransaction($data) {
        return Transactions::insert($data);
    }

}
