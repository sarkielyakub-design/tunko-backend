<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\DataBundle\IndexDataBundleRequest;
use App\Http\Requests\Admin\DataBundle\StoreDataBundleRequest;
use App\Http\Requests\Admin\DataBundle\UpdateDataBundleRequest;
use App\Http\Resources\Admin\DataBundleCollection;
use App\Http\Resources\Admin\DataBundleResource;
use App\Models\DataBundle;
use App\Services\Admin\DataBundleService;

class DataBundleController extends AdminController
{
    /**
     * Service
     */
    public function __construct(
        protected DataBundleService $service
    ) {
    }

    /**
     * List Bundles
     */
    public function index(
        IndexDataBundleRequest $request
    )
    {
        return new DataBundleCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Store Bundle
     */
    public function store(
        StoreDataBundleRequest $request
    )
    {
        $bundle = $this->service->store(
            $request->validated()
        );

        return $this->success(

            new DataBundleResource($bundle),

            'Data bundle created successfully.',

            201

        );
    }

    /**
     * Show Bundle
     */
    public function show(
        DataBundle $dataBundle
    )
    {
        return $this->success(

            new DataBundleResource(

                $this->service->show($dataBundle)

            )

        );
    }

    /**
     * Update Bundle
     */
    public function update(
        UpdateDataBundleRequest $request,
        DataBundle $dataBundle
    )
    {
        $bundle = $this->service->update(

            $dataBundle,

            $request->validated()

        );

        return $this->success(

            new DataBundleResource($bundle),

            'Data bundle updated successfully.'

        );
    }

    /**
     * Delete Bundle
     */
    public function destroy(
        DataBundle $dataBundle
    )
    {
        $this->service->destroy(
            $dataBundle
        );

        return $this->success(

            null,

            'Data bundle deleted successfully.'

        );
    }

    /**
     * Activate Bundle
     */
    public function activate(
        DataBundle $dataBundle
    )
    {
        $bundle = $this->service->activate(
            $dataBundle
        );

        return $this->success(

            new DataBundleResource($bundle),

            'Bundle activated successfully.'

        );
    }

    /**
     * Deactivate Bundle
     */
    public function deactivate(
        DataBundle $dataBundle
    )
    {
        $bundle = $this->service->deactivate(
            $dataBundle
        );

        return $this->success(

            new DataBundleResource($bundle),

            'Bundle deactivated successfully.'

        );
    }
}