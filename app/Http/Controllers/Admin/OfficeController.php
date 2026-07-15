<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Office\IndexOfficeRequest;
use App\Http\Requests\Admin\Office\StoreOfficeRequest;
use App\Http\Requests\Admin\Office\UpdateOfficeRequest;
use App\Http\Resources\Admin\OfficeCollection;
use App\Http\Resources\Admin\OfficeResource;
use App\Models\Office;
use App\Services\Admin\OfficeService;

class OfficeController extends AdminController
{
    /**
     * Office Service
     */
    public function __construct(
        protected OfficeService $service
    ) {
    }

    /**
     * Office List
     */
    public function index(
        IndexOfficeRequest $request
    )
    {
        return new OfficeCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Create Office
     */
    public function store(
        StoreOfficeRequest $request
    )
    {
        $office = $this->service->store(
            $request->validated()
        );

        return $this->success(

            new OfficeResource($office),

            'Office created successfully.',

            201

        );
    }

    /**
     * Show Office
     */
    public function show(
        Office $office
    )
    {
        return $this->success(

            new OfficeResource(

                $this->service->show($office)

            )

        );
    }

    /**
     * Update Office
     */
    public function update(
        UpdateOfficeRequest $request,
        Office $office
    )
    {
        $office = $this->service->update(

            $office,

            $request->validated()

        );

        return $this->success(

            new OfficeResource($office),

            'Office updated successfully.'

        );
    }

    /**
     * Delete Office
     */
    public function destroy(
        Office $office
    )
    {
        $this->service->destroy($office);

        return $this->success(

            null,

            'Office deleted successfully.'

        );
    }

    /**
     * Activate Office
     */
    public function activate(
        Office $office
    )
    {
        $office = $this->service->activate(
            $office
        );

        return $this->success(

            new OfficeResource($office),

            'Office activated successfully.'

        );
    }

    /**
     * Deactivate Office
     */
    public function deactivate(
        Office $office
    )
    {
        $office = $this->service->deactivate(
            $office
        );

        return $this->success(

            new OfficeResource($office),

            'Office deactivated successfully.'

        );
    }

    /**
     * Set Head Office
     */
    public function makeHeadOffice(
        Office $office
    )
    {
        $office = $this->service->makeHeadOffice(
            $office
        );

        return $this->success(

            new OfficeResource($office),

            'Head office updated successfully.'

        );
    }
}