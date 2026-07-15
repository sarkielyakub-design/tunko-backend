<?php

namespace App\Services\Admin;

use App\Models\Office;
use Illuminate\Support\Facades\DB;

class OfficeService
{
    /**
     * List Offices
     */
    public function index(array $filters)
    {
        return Office::query()

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                $filters['country'] ?? null,
                fn ($q, $country) =>
                    $q->where('country', $country)
            )

            ->when(
                $filters['city'] ?? null,
                fn ($q, $city) =>
                    $q->where('city', $city)
            )

            ->when(
                isset($filters['is_active']),
                fn ($q) =>
                    $q->where(
                        'is_active',
                        $filters['is_active']
                    )
            )

            ->when(
                isset($filters['is_head_office']),
                fn ($q) =>
                    $q->where(
                        'is_head_office',
                        $filters['is_head_office']
                    )
            )

            ->orderBy(
                $filters['sort'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show Office
     */
    public function show(Office $office): Office
    {
        return $office;
    }

    /**
     * Create Office
     */
    public function store(array $data): Office
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Only One Head Office
            |--------------------------------------------------------------------------
            */

            if (($data['is_head_office'] ?? false) === true) {

                Office::query()->update([
                    'is_head_office' => false,
                ]);

            }

            $office = Office::create($data);

            return $office->fresh();

        });
    }

    /**
     * Update Office
     */
    public function update(
        Office $office,
        array $data
    ): Office {

        return DB::transaction(function () use (
            $office,
            $data
        ) {

            if (
                isset($data['is_head_office']) &&
                $data['is_head_office']
            ) {

                Office::query()
                    ->where('id', '!=', $office->id)
                    ->update([
                        'is_head_office' => false,
                    ]);

            }

            $office->update($data);

            return $office->fresh();

        });

    }

    /**
     * Delete Office
     */
    public function destroy(Office $office): void
    {
        DB::transaction(function () use ($office) {

            $office->delete();

        });
    }

    /**
     * Activate
     */
    public function activate(
        Office $office
    ): Office {

        $office->update([
            'is_active' => true,
        ]);

        return $office->fresh();

    }

    /**
     * Deactivate
     */
    public function deactivate(
        Office $office
    ): Office {

        $office->update([
            'is_active' => false,
        ]);

        return $office->fresh();

    }

    /**
     * Set Head Office
     */
    public function makeHeadOffice(
        Office $office
    ): Office {

        return DB::transaction(function () use ($office) {

            Office::query()->update([
                'is_head_office' => false,
            ]);

            $office->update([
                'is_head_office' => true,
            ]);

            return $office->fresh();

        });

    }
}