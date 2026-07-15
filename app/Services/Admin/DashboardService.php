<?php

namespace App\Services\Admin;

use App\Models\Airtime;
use App\Models\DataPurchase;
use App\Models\Kyc;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;

class DashboardService
{
    public function dashboard(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Statistics Cards
            |--------------------------------------------------------------------------
            */

            "statistics" => [

                "users" => [

                    "total" => User::count(),

                    "verified" => User::where(
                        "is_verified",
                        true
                    )->count(),

                    "active" => User::where(
                        "is_active",
                        true
                    )->count(),

                    "today" => User::whereDate(
                        "created_at",
                        today()
                    )->count(),

                ],

                "wallet" => [

                    "total" => Wallet::count(),

                    "active" => Wallet::where(
                        "status",
                        "active"
                    )->count(),

                    "total_balance" => Wallet::sum(
                        "balance"
                    ),

                ],

                "transactions" => [

                    "total" => Transaction::count(),

                    "today" => Transaction::whereDate(
                        "created_at",
                        today()
                    )->count(),

                ],

                "transfers" => [

                    "total" => Transfer::count(),

                    "today" => Transfer::whereDate(
                        "created_at",
                        today()
                    )->count(),

                ],

                "airtime" => [

                    "total" => Airtime::count(),

                    "today" => Airtime::whereDate(
                        "created_at",
                        today()
                    )->count(),

                ],

                "data" => [

                    "total" => DataPurchase::count(),

                    "today" => DataPurchase::whereDate(
                        "created_at",
                        today()
                    )->count(),

                ],

                "kyc" => [

                    "pending" => Kyc::where(
                        "status",
                        "pending"
                    )->count(),

                    "approved" => Kyc::where(
                        "status",
                        "approved"
                    )->count(),

                    "rejected" => Kyc::where(
                        "status",
                        "rejected"
                    )->count(),

                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Weekly Chart Data
            |--------------------------------------------------------------------------
            */

            "charts" => [

                "users" => $this->lastSevenDaysUsers(),

                "transactions" => $this->lastSevenDaysTransactions(),

                "transfers" => $this->lastSevenDaysTransfers(),

            ],

            /*
            |--------------------------------------------------------------------------
            | Recent Activities
            |--------------------------------------------------------------------------
            */

            "recent_users" => User::latest()
                ->take(5)
                ->get(),

            "recent_transactions" => Transaction::latest()
                ->take(10)
                ->get(),

            "recent_transfers" => Transfer::latest()
                ->take(10)
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Pending Approvals
            |--------------------------------------------------------------------------
            */

            "pending_kyc" => Kyc::where(
                "status",
                "pending"
            )
                ->latest()
                ->take(5)
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | System Health
            |--------------------------------------------------------------------------
            */

            "system_health" => [

                "database" => true,

                "api" => true,

                "queue" => true,

                "cache" => true,

            ],

        ];
    }

    private function lastSevenDaysUsers(): array
    {
        return collect(range(6, 0))
            ->map(function ($day) {

                $date = Carbon::today()->subDays($day);

                return [

                    "date" => $date->format("D"),

                    "total" => User::whereDate(
                        "created_at",
                        $date
                    )->count(),

                ];

            })
            ->values()
            ->toArray();
    }

    private function lastSevenDaysTransactions(): array
    {
        return collect(range(6, 0))
            ->map(function ($day) {

                $date = Carbon::today()->subDays($day);

                return [

                    "date" => $date->format("D"),

                    "total" => Transaction::whereDate(
                        "created_at",
                        $date
                    )->count(),

                ];

            })
            ->values()
            ->toArray();
    }

    private function lastSevenDaysTransfers(): array
    {
        return collect(range(6, 0))
            ->map(function ($day) {

                $date = Carbon::today()->subDays($day);

                return [

                    "date" => $date->format("D"),

                    "total" => Transfer::whereDate(
                        "created_at",
                        $date
                    )->count(),

                ];

            })
            ->values()
            ->toArray();
    }
}