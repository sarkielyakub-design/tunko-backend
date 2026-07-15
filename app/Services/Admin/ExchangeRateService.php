<?php

namespace App\Services\Admin;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExchangeRateService
{
    /**
     * Exchange Rate List
     */
    public function index(array $filters)
    {
        return ExchangeRate::query()

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('base_currency', 'like', "%{$search}%")

                          ->orWhere('target_currency', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                $filters['base_currency'] ?? null,
                fn($q, $value) => $q->where('base_currency', $value)
            )

            ->when(
                $filters['target_currency'] ?? null,
                fn($q, $value) => $q->where('target_currency', $value)
            )

            ->when(
                $filters['source'] ?? null,
                fn($q, $value) => $q->where('source', $value)
            )

            ->when(
                isset($filters['is_manual']),
                fn($q) => $q->where('is_manual', $filters['is_manual'])
            )

            ->when(
                isset($filters['is_active']),
                fn($q) => $q->where('is_active', $filters['is_active'])
            )

            ->orderBy(
                $filters['sort'] ?? 'updated_at',
                $filters['direction'] ?? 'desc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Store Exchange Rate
     */
    public function store(array $data): ExchangeRate
    {
        return DB::transaction(function () use ($data) {

            $rate = ExchangeRate::create([

                'base_currency' => strtoupper($data['base_currency']),

                'target_currency' => strtoupper($data['target_currency']),

                'rate' => $data['rate'],

                'markup' => $data['markup'] ?? 0,

                'final_rate' => $this->calculateFinalRate(

                    $data['rate'],

                    $data['markup'] ?? 0

                ),

                'source' => $data['source'],

                'is_manual' => $data['is_manual'],

                'is_active' => $data['is_active'],

                'updated_by' => Auth::guard('admin')->id(),

                'note' => $data['note'] ?? null,

                'last_synced_at' => now(),

            ]);

            return $rate;

        });
    }

    /**
     * Show Exchange Rate
     */
    public function show(
        ExchangeRate $rate
    ): ExchangeRate {

        return $rate;

    }

    /**
     * Update Exchange Rate
     */
    public function update(
        ExchangeRate $rate,
        array $data
    ): ExchangeRate {

        return DB::transaction(function () use (
            $rate,
            $data
        ) {

            $rate->update([

                'rate' => $data['rate'],

                'markup' => $data['markup'] ?? 0,

                'final_rate' => $this->calculateFinalRate(

                    $data['rate'],

                    $data['markup'] ?? 0

                ),

                'source' => $data['source'],

                'is_manual' => $data['is_manual'],

                'is_active' => $data['is_active'],

                'updated_by' => Auth::guard('admin')->id(),

                'note' => $data['note'] ?? null,

                'last_synced_at' => now(),

            ]);

            return $rate->fresh();

        });
    }

    /**
     * Delete Exchange Rate
     */
    public function destroy(
        ExchangeRate $rate
    ): bool {

        return $rate->delete();

    }

    /**
     * Sync Exchange Rates
     *
     * NOTE:
     * Replace this placeholder implementation with
     * your provider integration (OpenExchangeRates,
     * Fixer, CurrencyLayer, etc.).
     */
    public function sync(array $data): array
    {
        return [

            'provider' => $data['provider'],

            'base_currency' => $data['base_currency'],

            'currencies' => $data['currencies'],

            'synced_at' => now(),

            'updated_by' => Auth::guard('admin')->id(),

            'status' => 'success',

            'message' => 'Exchange rates synchronized successfully.',

        ];
    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total_rates' => ExchangeRate::count(),

            'active_rates' => ExchangeRate::where(
                'is_active',
                true
            )->count(),

            'manual_rates' => ExchangeRate::where(
                'is_manual',
                true
            )->count(),

            'api_rates' => ExchangeRate::where(
                'is_manual',
                false
            )->count(),

            'last_sync' => optional(

                ExchangeRate::latest(
                    'last_synced_at'
                )->first()

            )->last_synced_at,

        ];
    }

    /**
     * Calculate Final Rate
     */
    protected function calculateFinalRate(
        float $rate,
        float $markup = 0
    ): float {

        return round(

            $rate + (($markup / 100) * $rate),

            6

        );

    }
}