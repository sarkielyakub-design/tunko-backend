<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\FundingController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\BeneficiaryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\KycController;
use App\Http\Controllers\Api\V1\PinController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Api\V1\DataController;
use App\Http\Controllers\Api\V1\AirtimeController;
use App\Http\Controllers\Api\V1\WalletDepositController;
require __DIR__.'/api/admin.php';
/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Tunko API is working',
    ]);
});
/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/forgot', [
        PasswordController::class,
        'forgot'
    ]);
    Route::post('/password/reset', [
        PasswordController::class,
        'reset'
    ]);
    Route::middleware('auth:sanctum')
    ->get('/me',[AuthController::class,'me']);
    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('wallet')->group(function () {

    Route::post(
        '/deposit/initialize',
        [WalletDepositController::class, 'initialize']
    );

    Route::post(
        '/deposit/verify',
        [WalletDepositController::class, 'verify']
    );

});
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */
        Route::post('/logout', [
            AuthController::class,
            'logout'
        ]);
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ]);
        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [
            ProfileController::class,
            'show'
        ]);
        Route::put('/profile', [
            ProfileController::class,
            'update'
        ]);
        /*
        |--------------------------------------------------------------------------
        | Wallet
        |--------------------------------------------------------------------------
        */
        Route::get('/wallet', [
            WalletController::class,
            'show'
        ]);
        Route::get('/wallet/balance', [
            WalletController::class,
            'balance'
        ]);
        Route::get('/wallet/summary', [
            WalletController::class,
            'summary'
        ]);
        Route::post('/wallet/fund', [
            FundingController::class,
            'fund'
        ]);
        Route::post('/wallet/deposit', [
            WalletController::class,
            'deposit'
        ]);
        Route::post('/wallet/transfer', [
            TransferController::class,
            'transfer'
        ]);
        
        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */
        Route::get('/transactions', [
            TransactionController::class,
            'index'
        ]);
        Route::middleware('auth:sanctum')->get(
    '/receipt/{reference}',
    [TransactionController::class, 'receipt']
);/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

Route::prefix('data')
    ->controller(DataController::class)
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Countries & Networks
        |--------------------------------------------------------------------------
        */

    Route::get('/countries', [DataController::class, 'countries']);

    Route::get('/networks/{country}', [DataController::class, 'networks']);

    Route::get('/bundles/{network}', [DataController::class, 'bundles']);

    Route::post('/quote', [DataController::class, 'quote']);

    Route::post('/purchase', [DataController::class, 'purchase']);

    Route::get('/beneficiaries', [DataController::class, 'beneficiaries']);

    Route::get('/history', [DataController::class, 'history']);

    Route::get('/receipt/{reference}', [DataController::class, 'receipt']);

});
/*
|--------------------------------------------------------------------------
| Transfers
|--------------------------------------------------------------------------
*/

Route::prefix('transfer')->group(function () {

    // Countries
    Route::get('/countries', [
        TransferController::class,
        'countries'
    ]);

    // Saved Recipients
    Route::get('/recipients', [
        TransferController::class,
        'recipients'
    ]);

    // Search Recipient
    Route::post('/search', [
        TransferController::class,
        'searchRecipient'
    ]);

    // Verify Recipient
    Route::post('/verify', [
        TransferController::class,
        'verify'
    ]);

    // Transfer Quote
    Route::post('/quote', [
        TransferController::class,
        'quote'
    ]);

    // Send Money
    Route::post('/send', [
        TransferController::class,
        'transfer'
    ]);

    // Transfer History
    Route::get('/history', [
        TransferController::class,
        'history'
    ]);

    // Receipt
    Route::get('/receipt/{reference}', [
        TransferController::class,
        'receipt'
    ]);


    Route::get("/beneficiaries", [BeneficiaryController::class, "index"]);

    Route::post("/beneficiaries", [BeneficiaryController::class, "store"]);

    Route::delete("/beneficiaries/{beneficiary}", [BeneficiaryController::class, "destroy"]);
});

        /*
        |--------------------------------------------------------------------------
        | Beneficiaries
        |--------------------------------------------------------------------------
        */
        Route::get('/beneficiaries', [
            BeneficiaryController::class,
            'index'
        ]);
        Route::post('/beneficiaries', [
            BeneficiaryController::class,
            'store'
        ]);
        Route::post('/beneficiaries/verify', [
            BeneficiaryController::class,
            'verify'
        ]);
        Route::delete('/beneficiaries/{beneficiary}', [
            BeneficiaryController::class,
            'destroy'
        ]);
        /*
        |--------------------------------------------------------------------------
        | KYC
        |--------------------------------------------------------------------------
        */
        Route::post('/kyc/submit', [
            KycController::class,
            'submit'
        ]);
        Route::get('/kyc/status', [
            KycController::class,
            'status'
        ]);
        /*
        |--------------------------------------------------------------------------
        | Transaction PIN
        |--------------------------------------------------------------------------
        */
        Route::post('/pin/create', [
            PinController::class,
            'create'
        ]);
        Route::post('/pin/verify', [
            PinController::class,
            'verify'
        ]);
        Route::post('/pin/change', [
            PinController::class,
            'change'
        ]);
        Route::post('/pin/reset', [
            PinController::class,
            'reset'
        ]);
        /*
        |--------------------------------------------------------------------------
        | OTP
        |--------------------------------------------------------------------------
        */
       Route::middleware('auth:sanctum')->group(function () {

    Route::post('/otp/send', [
        OtpController::class,
        'send',
    ]);

    Route::post('/otp/verify', [
        OtpController::class,
        'verify',
    ]);

});
        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */
        Route::post('/password/change', [
            PasswordController::class,
            'change'
        ]);
        /*
        |--------------------------------------------------------------------------
        | Cards
        |--------------------------------------------------------------------------
        */
        Route::get('/cards', [
            CardController::class,
            'index'
        ]);
        Route::post('/cards', [
            CardController::class,
            'store'
        ]);
        Route::delete('/cards/{id}', [
            CardController::class,
            'destroy'
        ]);
        Route::post('/cards/default/{id}', [
            CardController::class,
            'setDefault'
        ]);
        Route::post('/cards/freeze/{id}', [
            CardController::class,
            'freeze'
        ]);/*
        |--------------------------------------------------------------------------
        | Airtime
        |--------------------------------------------------------------------------
        */
        Route::prefix('airtime')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Countries
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/countries',
        [AirtimeController::class, 'countries']
    );

    /*
    |--------------------------------------------------------------------------
    | Networks
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/networks/{country}',
        [AirtimeController::class, 'networks']
    );

    /*
    |--------------------------------------------------------------------------
    | Quote
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/quote',
        [AirtimeController::class, 'quote']
    );

    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/purchase',
        [AirtimeController::class, 'purchase']
    );

    /*
    |--------------------------------------------------------------------------
    | Beneficiaries
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/beneficiaries',
        [AirtimeController::class, 'beneficiaries']
    );

    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/history',
        [AirtimeController::class, 'history']
    );

    /*
    |--------------------------------------------------------------------------
    | Receipt
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/receipt/{reference}',
        [AirtimeController::class, 'receipt']
    );

});
        /*
        |--------------------------------------------------------------------------
        | Exchange Rates
        |--------------------------------------------------------------------------
        */
        Route::get('/rates', [
            ExchangeRateController::class,
            'index'
        ]);
    });
});