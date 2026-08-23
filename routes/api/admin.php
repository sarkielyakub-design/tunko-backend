<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\NetworkController;
use App\Http\Controllers\Admin\DataBundleController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\ExchangeRateController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\AdminWalletController;
use App\Http\Controllers\Admin\ReloadlyController;
use App\Http\Controllers\Admin\ThunesController;
use App\Http\Controllers\Admin\VoucherController;


/*
|--------------------------------------------------------------------------
| ADMIN API
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PUBLIC ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login',
        [AuthController::class, 'login']
    );


    /*
    |--------------------------------------------------------------------------
    | PROTECTED ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATION
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/logout',
            [AuthController::class, 'logout']
        );

        Route::get(
            '/profile',
            [AuthController::class, 'profile']
        );


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        );


        /*
        |--------------------------------------------------------------------------
        | AUDIT LOGS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/audit-logs',
            [AuditLogController::class, 'index']
        );


        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')->group(function () {

            Route::get(
                '/',
                [UserController::class, 'index']
            );

            Route::post(
                '/',
                [UserController::class, 'store']
            );

            Route::get(
                '/{user}',
                [UserController::class, 'show']
            );

            Route::put(
                '/{user}',
                [UserController::class, 'update']
            );

            Route::delete(
                '/{user}',
                [UserController::class, 'destroy']
            );

            Route::get(
                '/{user}/details',
                [UserController::class, 'details']
            );

            Route::post(
                '/{user}/credit',
                [UserController::class, 'creditWallet']
            );

            Route::post(
                '/{user}/debit',
                [UserController::class, 'debitWallet']
            );

            Route::post(
                '/{user}/freeze',
                [UserController::class, 'freeze']
            );

            Route::post(
                '/{user}/unfreeze',
                [UserController::class, 'unfreeze']
            );

            Route::post(
                '/{user}/reset-password',
                [UserController::class, 'resetPassword']
            );

            Route::post(
                '/{user}/reset-pin',
                [UserController::class, 'resetPin']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | EXCHANGE RATES
        |--------------------------------------------------------------------------
        */

        Route::prefix('exchange-rates')->group(function () {

            Route::get(
                '/',
                [ExchangeRateController::class, 'index']
            );

            Route::get(
                '/statistics',
                [ExchangeRateController::class, 'statistics']
            );

            Route::post(
                '/',
                [ExchangeRateController::class, 'store']
            );

            Route::post(
                '/sync',
                [ExchangeRateController::class, 'sync']
            );

            Route::get(
                '/{exchangeRate}',
                [ExchangeRateController::class, 'show']
            );

            Route::put(
                '/{exchangeRate}',
                [ExchangeRateController::class, 'update']
            );

            Route::delete(
                '/{exchangeRate}',
                [ExchangeRateController::class, 'destroy']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | VOUCHERS
        |--------------------------------------------------------------------------
        |
        | Supplier cards/PIN inventory.
        |
        | IMPORTANT:
        | Because we are already inside:
        |
        | Route::prefix('admin')
        |
        | these become:
        |
        | GET  /api/admin/vouchers
        | POST /api/admin/vouchers
        |
        */

        Route::prefix('vouchers')->group(function () {

            Route::get(
                '/',
                [VoucherController::class, 'index']
            );

            Route::post(
                '/',
                [VoucherController::class, 'store']
            );

            Route::get(
                '/{voucher}',
                [VoucherController::class, 'show']
            );

            Route::delete(
                '/{voucher}',
                [VoucherController::class, 'destroy']
            );

            Route::post(
                '/{voucher}/cancel',
                [VoucherController::class, 'cancel']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | WITHDRAWALS
        |--------------------------------------------------------------------------
        */

        Route::prefix('withdrawals')->group(function () {

            Route::get(
                '/',
                [WithdrawalController::class, 'index']
            );

            Route::get(
                '/statistics',
                [WithdrawalController::class, 'statistics']
            );

            Route::get(
                '/{withdrawal}',
                [WithdrawalController::class, 'show']
            );

            Route::post(
                '/{withdrawal}/approve',
                [WithdrawalController::class, 'approve']
            );

            Route::post(
                '/{withdrawal}/reject',
                [WithdrawalController::class, 'reject']
            );

            Route::post(
                '/{withdrawal}/cancel',
                [WithdrawalController::class, 'cancel']
            );

            Route::post(
                '/{withdrawal}/retry',
                [WithdrawalController::class, 'retry']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | TRANSFERS
        |--------------------------------------------------------------------------
        */

        Route::prefix('transfers')->group(function () {

            Route::get(
                '/',
                [TransferController::class, 'index']
            );

            Route::get(
                '/statistics',
                [TransferController::class, 'statistics']
            );

            Route::get(
                '/{transfer}',
                [TransferController::class, 'show']
            );

            Route::post(
                '/{transfer}/approve',
                [TransferController::class, 'approve']
            );

            Route::post(
                '/{transfer}/reject',
                [TransferController::class, 'reject']
            );

            Route::post(
                '/{transfer}/cancel',
                [TransferController::class, 'cancel']
            );

            Route::post(
                '/{transfer}/retry',
                [TransferController::class, 'retry']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | DEPOSITS
        |--------------------------------------------------------------------------
        */

        Route::prefix('deposits')->group(function () {

            Route::get(
                '/',
                [DepositController::class, 'index']
            );

            Route::get(
                '/statistics',
                [DepositController::class, 'statistics']
            );

            Route::get(
                '/{deposit}',
                [DepositController::class, 'show']
            );

            Route::post(
                '/{deposit}/approve',
                [DepositController::class, 'approve']
            );

            Route::post(
                '/{deposit}/reject',
                [DepositController::class, 'reject']
            );

            Route::post(
                '/{deposit}/cancel',
                [DepositController::class, 'cancel']
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN WALLET
            |--------------------------------------------------------------------------
            */

            Route::prefix('admin-wallet')
                ->controller(AdminWalletController::class)
                ->group(function () {

                    Route::get(
                        '/',
                        'index'
                    );

                    Route::get(
                        '/transactions',
                        'transactions'
                    );

                    Route::post(
                        '/fund',
                        'fund'
                    );

                    Route::put(
                        '/{adminWallet}',
                        'update'
                    );
                });
        });


        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        Route::prefix('transactions')->group(function () {

            Route::get(
                '/',
                [TransactionController::class, 'index']
            );

            Route::get(
                '/statistics',
                [TransactionController::class, 'statistics']
            );

            Route::get(
                '/{transaction}',
                [TransactionController::class, 'show']
            );

            Route::post(
                '/{transaction}/refund',
                [TransactionController::class, 'refund']
            );

            Route::post(
                '/{transaction}/status',
                [TransactionController::class, 'updateStatus']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | KYCs
        |--------------------------------------------------------------------------
        */

        Route::prefix('kycs')->group(function () {

            Route::get(
                '/',
                [KycController::class, 'index']
            );

            Route::get(
                '/statistics',
                [KycController::class, 'statistics']
            );

            Route::get(
                '/{kyc}',
                [KycController::class, 'show']
            );

            Route::post(
                '/{kyc}/approve',
                [KycController::class, 'approve']
            );

            Route::post(
                '/{kyc}/reject',
                [KycController::class, 'reject']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | COUNTRIES
        |--------------------------------------------------------------------------
        */

        Route::prefix('countries')->group(function () {

            Route::get(
                '/',
                [CountryController::class, 'index']
            );

            Route::post(
                '/',
                [CountryController::class, 'store']
            );

            Route::get(
                '/{country}',
                [CountryController::class, 'show']
            );

            Route::put(
                '/{country}',
                [CountryController::class, 'update']
            );

            Route::delete(
                '/{country}',
                [CountryController::class, 'destroy']
            );

            Route::post(
                '/{country}/activate',
                [CountryController::class, 'activate']
            );

            Route::post(
                '/{country}/deactivate',
                [CountryController::class, 'deactivate']
            );

            Route::post(
                '/{country}/exchange-rate',
                [CountryController::class, 'updateExchangeRate']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | WALLETS
        |--------------------------------------------------------------------------
        */

        Route::prefix('wallets')->group(function () {

            Route::get(
                '/',
                [WalletController::class, 'index']
            );

            Route::get(
                '/summary',
                [WalletController::class, 'summary']
            );

            Route::get(
                '/{wallet}',
                [WalletController::class, 'show']
            );

            Route::post(
                '/{wallet}/credit',
                [WalletController::class, 'credit']
            );

            Route::post(
                '/{wallet}/debit',
                [WalletController::class, 'debit']
            );

            Route::post(
                '/{wallet}/freeze',
                [WalletController::class, 'freeze']
            );

            Route::post(
                '/{wallet}/unfreeze',
                [WalletController::class, 'unfreeze']
            );

            Route::get(
                '/{wallet}/statement',
                [WalletController::class, 'statement']
            );

            Route::get(
                '/{wallet}/transactions',
                [WalletController::class, 'transactions']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | NETWORKS
        |--------------------------------------------------------------------------
        */

        Route::prefix('networks')->group(function () {

            Route::get(
                '/',
                [NetworkController::class, 'index']
            );

            Route::post(
                '/',
                [NetworkController::class, 'store']
            );

            Route::get(
                '/{network}',
                [NetworkController::class, 'show']
            );

            Route::put(
                '/{network}',
                [NetworkController::class, 'update']
            );

            Route::delete(
                '/{network}',
                [NetworkController::class, 'destroy']
            );

            Route::post(
                '/{network}/activate',
                [NetworkController::class, 'activate']
            );

            Route::post(
                '/{network}/deactivate',
                [NetworkController::class, 'deactivate']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | DATA BUNDLES
        |--------------------------------------------------------------------------
        */

        Route::prefix('data-bundles')->group(function () {

            Route::get(
                '/',
                [DataBundleController::class, 'index']
            );

            Route::post(
                '/',
                [DataBundleController::class, 'store']
            );

            Route::get(
                '/{dataBundle}',
                [DataBundleController::class, 'show']
            );

            Route::put(
                '/{dataBundle}',
                [DataBundleController::class, 'update']
            );

            Route::delete(
                '/{dataBundle}',
                [DataBundleController::class, 'destroy']
            );

            Route::post(
                '/{dataBundle}/activate',
                [DataBundleController::class, 'activate']
            );

            Route::post(
                '/{dataBundle}/deactivate',
                [DataBundleController::class, 'deactivate']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | OFFICES
        |--------------------------------------------------------------------------
        */

        Route::prefix('offices')->group(function () {

            Route::get(
                '/',
                [OfficeController::class, 'index']
            );

            Route::post(
                '/',
                [OfficeController::class, 'store']
            );

            Route::get(
                '/{office}',
                [OfficeController::class, 'show']
            );

            Route::put(
                '/{office}',
                [OfficeController::class, 'update']
            );

            Route::delete(
                '/{office}',
                [OfficeController::class, 'destroy']
            );

            Route::post(
                '/{office}/activate',
                [OfficeController::class, 'activate']
            );

            Route::post(
                '/{office}/deactivate',
                [OfficeController::class, 'deactivate']
            );

            Route::post(
                '/{office}/head-office',
                [OfficeController::class, 'makeHeadOffice']
            );
        });


        /*
        |--------------------------------------------------------------------------
        | RELOADLY HEALTH
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reloadly/health',
            [ReloadlyController::class, 'health']
        );


        /*
        |--------------------------------------------------------------------------
        | THUNES HEALTH
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/thunes/health',
            [ThunesController::class, 'health']
        );

    });
});