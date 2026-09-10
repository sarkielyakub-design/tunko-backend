<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Network\IndexNetworkRequest;
use App\Http\Requests\Admin\Network\StoreNetworkRequest;
use App\Http\Requests\Admin\Network\UpdateNetworkRequest;
use App\Http\Resources\Admin\NetworkCollection;
use App\Http\Resources\Admin\NetworkResource;
use App\Models\Network;
use App\Services\Admin\NetworkService;

class NetworkController extends AdminController
{
    /**
     * Service
     */
    public function __construct(
        protected NetworkService $service
    ) {
    }

    /**
     * List Networks
     */
    public function index(
        IndexNetworkRequest $request
    )
    {
        return new NetworkCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Create Network
     */
    public function store(
        StoreNetworkRequest $request
    )
    {
        $network = $this->service->store(
            $request->validated()
        );

        return $this->success(

            new NetworkResource($network),

            'Network created successfully.',

            201

        );
    }

    /**
     * Show Network
     */
    public function show(
        Network $network
    )
    {
        return $this->success(

            new NetworkResource(

                $this->service->show($network)

            )

        );
    }

    /**
     * Update Network
     */
    public function update(
        UpdateNetworkRequest $request,
        Network $network
    )
    {
        $network = $this->service->update(

            $network,

            $request->validated()

        );

        return $this->success(

            new NetworkResource($network),

            'Network updated successfully.'

        );
    }

    /**
     * Delete Network
     */
    public function destroy(
        Network $network
    )
    {
        $this->service->destroy($network);

        return $this->success(

            null,

            'Network deleted successfully.'

        );
    }

    /**
     * Activate Network
     */
    public function activate(
        Network $network
    )
    {
        $network = $this->service->activate(
            $network
        );

        return $this->success(

            new NetworkResource($network),

            'Network activated successfully.'

        );
    }

    /**
     * Deactivate Network
     */
    public function deactivate(
        Network $network
    )
    {
        $network = $this->service->deactivate(
            $network
        );

        return $this->success(

            new NetworkResource($network),

            'Network deactivated successfully.'

        );
    }
}