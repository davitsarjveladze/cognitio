composer install
npm install
npm run dev
php artisan migrate
php artisan DB:seed
php artisan get:currencies - ვალუტისთვის


/api/auth/register -  ['email' => '', 'password'=> '' , 'password_confirmation' => '' , ''name' => '']
/api/auth/login - ['email' => '', 'password'=> '']

currency_id - რომელიც currency_listშია დამატებული და  currency_code რომელიც იქაა მითითებული
/api/wallet/create -  ['name' => '', 'currency_id'=> '' , 'currency_code' => '']


შიდა საფულეებს შორის შეცვლა 
/api/transaction/exchangeByWalletId => ['sender_wallet_id' => '', 'receiver_wallet_id'=> '' , 'value' => '']
/api/transaction/transactionBetweenToUsers =>  ['sender_wallet_id' => '', 'receiver_wallet_id'=> '' , 'value' => '','receiver_id' =>'']
