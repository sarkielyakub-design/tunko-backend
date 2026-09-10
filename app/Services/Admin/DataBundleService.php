<?php

namespace App\Services\Admin;

use App\Models\DataBundle;
use Illuminate\Support\Facades\DB;

class DataBundleService
{
    /**
     * List Bundles
     */
    public function index(array $filters)
    {
        return DataBundle::query()

            ->with([
                'country',
                'network',
            ])

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('size', 'like', "%{$search}%")
                            ->orWhere('provider_bundle_id', 'like', "%{$search}%")
                            ->orWhere('provider', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                $filters['country_id'] ?? null,
                fn($q, $country) =>
                $q->where('country_id', $country)
            )

            ->when(
                $filters['network_id'] ?? null,
                fn($q, $network) =>
                $q->where('network_id', $network)
            )

            ->when(
                $filters['provider'] ?? null,
                fn($q, $provider) =>
                $q->where('provider', $provider)
            )

            ->when(
                isset($filters['is_active']),
                fn($q) =>
                $q->where(
                    'is_active',
                    $filters['is_active']
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
     * Show Bundle
     */
    public function show(
        DataBundle $bundle
    ): DataBundle {

        return $bundle->load([
            'country',
            'network',
        ]);

    }

    /**
     * Create Bundle
     */
    public function store(
        array $data
    ): DataBundle {

        return DB::transaction(function () use ($data) {

            return DataBundle::create($data)
                ->load([
                    'country',
                    'network',
                ]);

        });

    }

    /**
     * Update Bundle
     */
    public function update(
        DataBundle $bundle,
        array $data
    ): DataBundle {

        return DB::transaction(function () use (
            $bundle,
            $data
        ) {

            $bundle->update($data);

            return $bundle->fresh()
                ->load([
                    'country',
                    'network',
                ]);

        });

    }

    /**
     * Delete Bundle
     */
    public function destroy(
        DataBundle $bundle
    ): void {

        DB::transaction(function () use ($bundle) {

            $bundle->delete();

        });

    }

    /**
     * Activate Bundle
     */
    public function activate(
        DataBundle $bundle
    ): DataBundle {

        $bundle->update([

            'is_active' => true,

        ]);

        return $bundle->fresh()
            ->load([
                'country',
                'network',
            ]);

    }

    /**
     * Deactivate Bundle
     */
    public function deactivate(
        DataBundle $bundle
    ): DataBundle {

        $bundle->update([

            'is_active' => false,

        ]);

        return $bundle->fresh()
            ->load([
                'country',
                'network',
            ]);

    }
}