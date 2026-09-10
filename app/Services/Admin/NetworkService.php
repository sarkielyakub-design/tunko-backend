<?php

namespace App\Services\Admin;

use App\Models\Network;
use Illuminate\Support\Facades\DB;

class NetworkService
{
    /**
     * List Networks
     */
    public function index(array $filters)
    {
        return Network::query()

            ->with('country')

            ->withCount('dataBundles')

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('provider', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                $filters['country_id'] ?? null,
                fn ($q, $country) =>
                    $q->where('country_id', $country)
            )

            ->when(
                isset($filters['airtime_enabled']),
                fn ($q) =>
                    $q->where(
                        'airtime_enabled',
                        $filters['airtime_enabled']
                    )
            )

            ->when(
                isset($filters['data_enabled']),
                fn ($q) =>
                    $q->where(
                        'data_enabled',
                        $filters['data_enabled']
                    )
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
                $filters['sort'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show Network
     */
    public function show(Network $network): Network
    {
        return $network->load(
            'country'
        )->loadCount(
            'dataBundles'
        );
    }

    /**
     * Create Network
     */
    public function store(array $data): Network
    {
        return DB::transaction(function () use ($data) {

            return Network::create($data)
                ->load('country')
                ->loadCount('dataBundles');

        });
    }

    /**
     * Update Network
     */
    public function update(
        Network $network,
        array $data
    ): Network {

        return DB::transaction(function () use (
            $network,
            $data
        ) {

            $network->update($data);

            return $network->fresh()
                ->load('country')
                ->loadCount('dataBundles');

        });

    }

    /**
     * Delete Network
     */
    public function destroy(
        Network $network
    ): void {

        DB::transaction(function () use ($network) {

            $network->delete();

        });

    }

    /**
     * Activate Network
     */
    public function activate(
        Network $network
    ): Network {

        $network->update([
            'is_active' => true,
        ]);

        return $network->fresh()
            ->load('country')
            ->loadCount('dataBundles');
    }

    /**
     * Deactivate Network
     */
    public function deactivate(
        Network $network
    ): Network {

        $network->update([
            'is_active' => false,
        ]);

        return $network->fresh()
            ->load('country')
            ->loadCount('dataBundles');
    }
}