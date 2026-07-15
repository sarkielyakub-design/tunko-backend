<?php

namespace App\Services\Admin;

use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CountryService
{
    /**
     * List Countries
     */
    public function index(array $filters)
    {
        return Country::query()

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('official_name', 'like', "%{$search}%")
                            ->orWhere('iso2', 'like', "%{$search}%")
                            ->orWhere('iso3', 'like', "%{$search}%")
                            ->orWhere('currency', 'like', "%{$search}%")
                            ->orWhere('currency_name', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                isset($filters['is_active']),
                fn ($q) =>
                    $q->where(
                        'is_active',
                        $filters['is_active']
                    )
            )

            ->orderBy(
                $filters['sort'] ?? 'name',
                $filters['direction'] ?? 'asc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show Country
     */
    public function show(Country $country): Country
    {
        return $country;
    }

    /**
     * Create Country
     */
    public function store(array $data): Country
    {
        return DB::transaction(function () use ($data) {

            return Country::create($data);

        });
    }

    /**
     * Update Country
     */
    public function update(
        Country $country,
        array $data
    ): Country {

        return DB::transaction(function () use (
            $country,
            $data
        ) {

            $country->update($data);

            return $country->fresh();

        });

    }

    /**
     * Delete Country
     */
    public function destroy(
        Country $country
    ): void {

        DB::transaction(function () use ($country) {

            $country->delete();

        });

    }

    /**
     * Activate Country
     */
    public function activate(
        Country $country
    ): Country {

        $country->update([

            'is_active' => true,

        ]);

        return $country->fresh();

    }

    /**
     * Deactivate Country
     */
    public function deactivate(
        Country $country
    ): Country {

        $country->update([

            'is_active' => false,

        ]);

        return $country->fresh();

    }

    /**
     * Update Exchange Rate
     */
    public function updateExchangeRate(
        Country $country,
        float $rate
    ): Country {

        $country->update([

            'exchange_rate' => $rate,

        ]);

        return $country->fresh();

    }
}