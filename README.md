composer install <br> 
npm install<br>
npm run dev<br>
php artisan migrate<br>
php artisan DB:seed<br>
php artisan get:currencies - ვალუტისთვის<br>


/api/auth/register -  ['email' => '', 'password'=> '' , 'password_confirmation' => '' , ''name' => '']<br>
/api/auth/login - ['email' => '', 'password'=> '']<br>

currency_id - რომელიც currency_listშია დამატებული და  currency_code რომელიც იქაა მითითებული<br>
/api/wallet/create -  ['name' => '', 'currency_id'=> '' , 'currency_code' => '']<br>


შიდა საფულეებს შორის შეცვლა <br>
/api/transaction/exchangeByWalletId => ['sender_wallet_id' => '', 'receiver_wallet_id'=> '' , 'value' => '']<br>
მომხმარებლებს შორის ტრანზაქცია <br>
/api/transaction/transactionBetweenToUsers =>  ['sender_wallet_id' => '', 'receiver_wallet_id'=> '' , 'value' => '','receiver_id' =>'']<br>
