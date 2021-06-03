<?php

namespace App\Http\Controllers;

use App\Helpers\currency;
use App\Models\Wallet;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Psy\Util\Json;

use App\Models\ExchangeRate;


class WalletController extends Controller
{
    public function create(Request $request) {

        $data = Validator::make($request->all(), [
            'name' => 'required|string',
            'currency_id' => 'required|Integer',
            'currency_code' => 'required'
        ])->validate();

        $data['user_id'] = Auth::id();

        //შემოწმება არსებობს თუ არაა ესეთი საფულე
        if (!Wallet::IsWallet($data['user_id'],$data['currency_id'])) {
            //საფულეს შექმნა თუ არ არსებობს
            if (($createdWallet = Wallet::create($data)))
                return response()->json([
                    'status' => 1,
                    'data' => $createdWallet
                ]);
            else {
                //შეცდომის დაბრუნება თუ საფულე არ არსებობს მაგრამ ვერ შეიქმნა
                return response()->json([
                    'status' => 0,
                    'error' => 'Could not create wallet try again'
                ]);
            }
        }
        // შეცდომის დაბრუნება თუ საფულე უკვე არსებობს
        return response()->json([
            'status'=> 0,
            'error' => 'This wallet exist'
        ]);
    }
}
