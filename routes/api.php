<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\FundingController;
use App\Http\Controllers\Api\V1\WalletDepositController;
use App\Http\Controllers\Api\V1\CinetPayController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\WalletTransferController;
use App\Http\Controllers\Api\V1\BeneficiaryController;
use App\Http\Controllers\Api\V1\DataController;
use App\Http\Controllers\Api\V1\AirtimeController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Api\V1\KycController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\PinController;

require __DIR__.'/api/admin.php';

/*
|--------------------------------------------------------------------------
| Test Route
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
    | Authentication (Public)
    |--------------------------------------------------------------------------
    */

    Route::post('/register', [
        AuthController::class,
        'register',
    ]);

    Route::post('/login', [
        AuthController::class,
        'login',
    ]);

    Route::post('/password/forgot', [
        PasswordController::class,
        'forgot',
    ]);

    Route::post('/password/reset', [
        PasswordController::class,
        'reset',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [
            ProfileController::class,
            'show',
        ]);

        Route::put('/profile', [
            ProfileController::class,
            'update',
        ]);
        /*
        |--------------------------------------------------------------------------
        | Wallet
        |--------------------------------------------------------------------------
        */

        Route::prefix('wallet')->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Wallet Details
            |--------------------------------------------------------------------------
            */

            Route::get('/', [
                WalletController::class,
                'show',
            ]);

            Route::get('/balance', [
                WalletController::class,
                'balance',
            ]);

            Route::get('/summary', [
                WalletController::class,
                'summary',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Wallet Operations
            |--------------------------------------------------------------------------
            */

            Route::post('/fund', [
                FundingController::class,
                'fund',
            ]);

            Route::post('/deposit', [
                WalletController::class,
                'deposit',
            ]);

            Route::post('/transfer', [
                TransferController::class,
                'transfer',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Manual Deposit Request
            |--------------------------------------------------------------------------
            */

            Route::post('/deposit/request', [
                WalletDepositController::class,
                'requestDeposit',
            ]);

            /*
            |--------------------------------------------------------------------------
            | CinetPay Deposit
            |--------------------------------------------------------------------------
            */

            Route::post('/deposit/initialize', [
                CinetPayController::class,
                'initialize',
            ]);

            Route::post('/deposit/verify', [
                CinetPayController::class,
                'verify',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | CinetPay Webhook
        |--------------------------------------------------------------------------
        |
        | No auth middleware should protect provider webhooks.
        |--------------------------------------------------------------------------
        */

        Route::post('/webhooks/cinetpay', [
            CinetPayController::class,
            'webhook',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */
Route::get('/reloadly-test', function () {

    $response = Illuminate\Support\Facades\Http::baseUrl(
        config('reloadly.topup_url')
    )
    ->timeout(30)
    ->withHeaders([
        'Authorization' => 'Bearer '.app(\App\Services\Reloadly\Auth\ReloadlyAuthService::class)->token(),
        'Accept' => 'application/com.reloadly.topups-v1+json',
        'Content-Type' => 'application/json',
    ])
    ->get('/countries');

    return response()->json([
        'status' => $response->status(),
        'body' => $response->json(),
    ]);
});
    Route::prefix('transactions')->group(function () {

        Route::get('/', [
            TransactionController::class,
            'index',
        ]);

        Route::get('/receipt/{reference}', [
            TransactionController::class,
            'receipt',
        ]);

    });

    /*
    |--------------------------------------------------------------------------
    | Data (Reloadly)
    |--------------------------------------------------------------------------
    */

        Route::prefix('data')->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Countries
            |--------------------------------------------------------------------------
            */

            Route::get('/countries', [
                DataController::class,
                'countries',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Networks / Operators
            |--------------------------------------------------------------------------
            */

            Route::get('/networks/{country}', [
                DataController::class,
                'networks',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Bundles / Products
            |--------------------------------------------------------------------------
            */

            Route::get('/bundles/{network}', [
                DataController::class,
                'bundles',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Quote
            |--------------------------------------------------------------------------
            */

            Route::post('/quote', [
                DataController::class,
                'quote',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Purchase
            |--------------------------------------------------------------------------
            */

            Route::post('/purchase', [
                DataController::class,
                'purchase',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Purchase History
            |--------------------------------------------------------------------------
            */

            Route::get('/history', [
                DataController::class,
                'history',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Receipt
            |--------------------------------------------------------------------------
            */

            Route::get('/receipt/{reference}', [
                DataController::class,
                'receipt',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Beneficiaries
            |--------------------------------------------------------------------------
            */

            Route::get('/beneficiaries', [
                DataController::class,
                'beneficiaries',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | International Transfer
        |--------------------------------------------------------------------------
        */

        Route::prefix('transfer')->group(function () {

            Route::get('/countries', [
                TransferController::class,
                'countries',
            ]);

            Route::get('/recipients', [
                TransferController::class,
                'recipients',
            ]);

            Route::post('/search', [
                TransferController::class,
                'searchRecipient',
            ]);

            Route::post('/verify', [
                TransferController::class,
                'verify',
            ]);

            Route::post('/quote', [
                TransferController::class,
                'quote',
            ]);

            Route::post('/send', [
                TransferController::class,
                'transfer',
            ]);

            Route::get('/history', [
                TransferController::class,
                'history',
            ]);

            Route::get('/receipt/{reference}', [
                TransferController::class,
                'receipt',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Wallet Transfer
        |--------------------------------------------------------------------------
        */

        Route::prefix('wallet-transfer')->group(function () {

            Route::post('/verify', [
                WalletTransferController::class,
                'verify',
            ]);

            Route::post('/quote', [
                WalletTransferController::class,
                'quote',
            ]);

            Route::post('/send', [
                WalletTransferController::class,
                'send',
            ]);

            Route::get('/history', [
                WalletTransferController::class,
                'history',
            ]);

            Route::get('/receipt/{reference}', [
                WalletTransferController::class,
                'receipt',
            ]);

            Route::get('/beneficiaries', [
                WalletTransferController::class,
                'beneficiaries',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Beneficiaries
        |--------------------------------------------------------------------------
        */

        Route::prefix('beneficiaries')->group(function () {

            Route::get('/', [
                BeneficiaryController::class,
                'index',
            ]);

            Route::post('/', [
                BeneficiaryController::class,
                'store',
            ]);

            Route::post('/verify', [
                BeneficiaryController::class,
                'verify',
            ]);

            Route::delete('/{beneficiary}', [
                BeneficiaryController::class,
                'destroy',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | KYC
        |--------------------------------------------------------------------------
        */

        Route::prefix('kyc')->group(function () {

            Route::post('/submit', [
                KycController::class,
                'submit',
            ]);

            Route::get('/status', [
                KycController::class,
                'status',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Transaction PIN
        |--------------------------------------------------------------------------
        */

        Route::prefix('pin')->group(function () {

            Route::post('/create', [
                PinController::class,
                'create',
            ]);

            Route::post('/verify', [
                PinController::class,
                'verify',
            ]);

            Route::post('/change', [
                PinController::class,
                'change',
            ]);

            Route::post('/reset', [
                PinController::class,
                'reset',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | OTP
        |--------------------------------------------------------------------------
        */

        Route::prefix('otp')->group(function () {

            Route::post('/send', [
                OtpController::class,
                'send',
            ]);

            Route::post('/verify', [
                OtpController::class,
                'verify',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        Route::prefix('password')->group(function () {

            Route::post('/change', [
                PasswordController::class,
                'change',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Cards
        |--------------------------------------------------------------------------
        */

        Route::prefix('cards')->group(function () {

            Route::get('/', [
                CardController::class,
                'index',
            ]);

            Route::post('/', [
                CardController::class,
                'store',
            ]);

            Route::delete('/{id}', [
                CardController::class,
                'destroy',
            ]);

            Route::post('/default/{id}', [
                CardController::class,
                'setDefault',
            ]);

            Route::post('/freeze/{id}', [
                CardController::class,
                'freeze',
            ]);

        });

        /*
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

            Route::get('/countries', [
                AirtimeController::class,
                'countries',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Networks / Operators
            |--------------------------------------------------------------------------
            */

            Route::get('/networks/{country}', [
                AirtimeController::class,
                'networks',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Products / Bundles
            |--------------------------------------------------------------------------
            */

            Route::get('/products/{network}', [
                AirtimeController::class,
                'products',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Quote
            |--------------------------------------------------------------------------
            */

            Route::post('/quote', [
                AirtimeController::class,
                'quote',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Purchase
            |--------------------------------------------------------------------------
            */

            Route::post('/purchase', [
                AirtimeController::class,
                'purchase',
            ]);

            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            Route::get('/history', [
                AirtimeController::class,
                'history',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Receipt
            |--------------------------------------------------------------------------
            */

            Route::get('/receipt/{reference}', [
                AirtimeController::class,
                'receipt',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Beneficiaries
            |--------------------------------------------------------------------------
            */

            Route::get('/beneficiaries', [
                AirtimeController::class,
                'beneficiaries',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | Exchange Rates
        |--------------------------------------------------------------------------
        */

        Route::get('/rates', [
            ExchangeRateController::class,
            'index',
        ]);

    });

});