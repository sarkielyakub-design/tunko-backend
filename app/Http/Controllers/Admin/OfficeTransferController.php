<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Office\IndexOfficeTransferRequest;
use App\Http\Requests\Admin\Office\UpdateOfficeTransferStatusRequest;
use App\Http\Resources\Admin\OfficeTransferResource;
use App\Models\OfficeTransfer;
use App\Services\Admin\OfficeTransferService;

class OfficeTransferController extends AdminController
{
    public function __construct(private OfficeTransferService $service) {}

    public function index(IndexOfficeTransferRequest $request)
    {
        return OfficeTransferResource::collection($this->service->index($request->validated()));
    }

    public function show(OfficeTransfer $officeTransfer)
    {
        return $this->success(new OfficeTransferResource($this->service->show($officeTransfer)));
    }

    public function updateStatus(UpdateOfficeTransferStatusRequest $request, OfficeTransfer $officeTransfer)
    {
        $data = $request->validated();
        $transfer = $this->service->updateStatus($officeTransfer, $data['status'], $data['reason'] ?? null);
        return $this->success(new OfficeTransferResource($transfer), 'Office transfer status updated successfully.');
    }
}
