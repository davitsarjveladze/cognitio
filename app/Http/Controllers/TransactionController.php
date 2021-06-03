<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\ExchangeRate;

class TransactionController extends Controller
{
    // მომხმარებლის საფულეებს შორის ტრანზაქციაა
    public function exchangeByWalletId(Request $request): \Illuminate\Http\JsonResponse
    {
        // ტრანზაქციისთვის მონაცემების მომზადება
        $data = self::prepareTransactionData(2,$request->all());

        // გამგზავნის საფულის არჩევა
        $SenderWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['sender_wallet_id']]);
        // შემოწმება არის თუ არა საფულე
        if (count($SenderWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'not found']);
        }
        // მიმღების საფულის არჩევა
        $ReceiverWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['receiver_wallet_id']]);
        // შემომწმება არის თუ არა მიმღების საფულე
        if (count($ReceiverWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'ანგარიში არ არსებობს']);
        }
        // ტრანზაქციის გაკეთბა
        return self::makeTransaction($data,$ReceiverWallet,$SenderWallet,0);

    }
    public function transactionBetweenToUsers(Request $request): \Illuminate\Http\JsonResponse
    {
        // ტრანზაქციისთვის მონაცემების მომზადება
        $data = self::prepareTransactionData(1,$request->all());
        // გამგზავნის საფულის არჩევა
        $SenderWallet = Wallet::getWalletWithCurrency(['user_id' => Auth::id(), 'wallet_id' => $data['sender_wallet_id']]);
        // შემოწმება არის თუ არა საფულე
        if (count($SenderWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'not found']);
        }

        // მიმღების საფულის არჩევა
        $ReceiverWallet = Wallet::getWalletWithCurrency(['user_id' => $data['receiver_id'], 'wallet_id' => $data['receiver_wallet_id']]);
        // შემომწმება არის თუ არა მიმღების საფულე
        if (count($ReceiverWallet) === 0){
            return response()->json(['status' => 0, 'error' => 'ანგარიში არ არსებობს']);
        }
        // შემოწმება ორივე საფულის ვალუტა თუ ემთხვვა ერთმანეთს
        if($SenderWallet[0]->currency_code !== $ReceiverWallet[0]->currency_code) {
            return response()->json(['status' => 0, 'error' => 'ვალუტა არ ემთხვევა']);
        }

        // ტრანზაქციის გაკეთბა
        return self::makeTransaction($data,$ReceiverWallet,$SenderWallet,1);

    }


    private static function prepareTransactionData($type_id, $request): array
    {
        // წესების განსაზღვრა
        $rulers = [
            'sender_wallet_id' => 'required|integer',
            'receiver_wallet_id' => 'required|integer',
            'value' => 'required|numeric',
        ];
        // თუ ტრანზაქცია ორ მომხმარებლებს შორისაა მიმღების აიდის დამატება
        if ($type_id === 1) {
            $rulers['receiver_id'] = 'required|integer';
        }
        // რექვესტის ვალიდაცია
        $data = Validator::make($request,$rulers)->validate();
        // გამგზავნის აიდის მიღება
        $data['sender_id'] = Auth::id();

        // თუ მომხმარებლის საფულეებს შორის ტრანზაქციაა receiver_id განსაზღვრა
        if ($type_id === 2)
            $data['receiver_id'] = Auth::id();

        $data['type_id'] = $type_id;
        $data['status_id'] = 1;
        // შემოსული თანხის თეთრებში გადაყვანა
        $data['value'] = $data['value'] * 100;

        return $data;
    }

    private static function makeTransaction($data,$ReceiverWallet,$SenderWallet,$Percent): \Illuminate\Http\JsonResponse
    {
        // საკომისიოს პროცენტის გათვლა
        $percentPlus = $data['value'] * $Percent/100;
        // შემოწმება არის თუ არა საკმარისი თანხა
        if ($SenderWallet[0]->balance >= ($data['value'] + $percentPlus)) {
            //გამგზავნის ახალი ბალანსის განსაზღვრა
            $newSenderBalance = $SenderWallet[0]->balance - $data['value'] - $percentPlus;
            //ვალუტის კურსის მიღება
            $exchangeRate = self::getExchangeRate($SenderWallet[0]->currency_code,$ReceiverWallet[0]->currency_code);
            //მიმღების ახალი ბალანსისი განსაზღვრა
            $newReceiverBalance = $ReceiverWallet[0]->balance + ($data['value']/($exchangeRate[0]->buy/100));
            //ბალანსის განახლება გამგზავნისთვის
            if(!self::updateWallet($SenderWallet[0]->wallet_id,['balance' => $newSenderBalance])) {
                return response()->json(['status' => 0, 'error' => 'ბლანსი ვერ განახლდა']);
            }
            //ბალანსის განახლება მიმღებისთვის
            if (!self::updateWallet($ReceiverWallet[0]->wallet_id,['balance' => $newReceiverBalance])){
                return response()->json(['status' => 0, 'error' => 'ბლანსი ვერ განახლდა']);
            }

            $data['currency_code'] = $SenderWallet[0]->currency_code;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            // ტრანზაქციის ჩაწერა
            if (!self::createTransaction($data)) {
                return response()->json(['status' => 0, 'error' => 'ტრანაზაქცია ვერ შეინახა']);
            }
        } else {
            return response()->json(['status' => 0, 'error' => 'ანგარიშზე არასაკმარისი თანხაა']);
        }

        return  response()->json(['status' => 1, 'data' => 'success']);
    }

    //ვალუტის კურსის მიღბა
    private static function getExchangeRate($from_currency,$to_currency) {
        return ExchangeRate::where('from_currency','=',$from_currency)
            ->where('to_currency','=',$to_currency)->get();
    }
    // საფულის განახლება
    private static function updateWallet($id,$updateData) {
        return Wallet::where('wallet_id','=',$id)->update($updateData);
    }
    // ტრანზაქციის შექმნა
    private static function createTransaction($data) {
        return Transactions::insert($data);
    }

}
