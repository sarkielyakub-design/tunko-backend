<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Office\IndexOfficeStaffRequest;
use App\Http\Requests\Admin\Office\StoreOfficeStaffRequest;
use App\Http\Requests\Admin\Office\UpdateOfficeStaffRequest;
use App\Http\Resources\Admin\OfficeStaffResource;
use App\Models\OfficeStaff;
use App\Services\Admin\OfficeStaffService;

class OfficeStaffController extends AdminController
{
    public function __construct(private OfficeStaffService $service) {}

    public function index(IndexOfficeStaffRequest $request)
    {
        return OfficeStaffResource::collection($this->service->index($request->validated()));
    }

    public function store(StoreOfficeStaffRequest $request)
    {
        return $this->success(new OfficeStaffResource($this->service->store($request->validated())), 'Office staff created successfully.', 201);
    }

    public function show(OfficeStaff $officeStaff)
    {
        return $this->success(new OfficeStaffResource($officeStaff->load('office')));
    }

    public function update(UpdateOfficeStaffRequest $request, OfficeStaff $officeStaff)
    {
        return $this->success(new OfficeStaffResource($this->service->update($officeStaff, $request->validated())), 'Office staff updated successfully.');
    }

    public function destroy(OfficeStaff $officeStaff)
    {
        $this->service->destroy($officeStaff);
        return $this->success(null, 'Office staff deleted successfully.');
    }

    public function activate(OfficeStaff $officeStaff)
    {
        return $this->success(new OfficeStaffResource($this->service->setActive($officeStaff, true)), 'Office staff activated successfully.');
    }

    public function deactivate(OfficeStaff $officeStaff)
    {
        return $this->success(new OfficeStaffResource($this->service->setActive($officeStaff, false)), 'Office staff deactivated successfully.');
    }
}
