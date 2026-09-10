<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Http\Requests\Office\UpdateTransferStatusRequest;
use App\Http\Resources\Office\OfficeTransferResource;
use App\Models\OfficeTransfer;
use App\Services\Office\OfficeTransferService;
use Illuminate\Http\Request;
use Throwable;

class TransferController extends Controller
{
    public function __construct(private OfficeTransferService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,processing,completed,failed,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        return OfficeTransferResource::collection($this->service->index($request->user(), $filters));
    }

    public function show(Request $request, OfficeTransfer $officeTransfer)
    {
        try {
            return response()->json(['success' => true, 'data' => new OfficeTransferResource($this->service->show($request->user(), $officeTransfer))]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    public function updateStatus(UpdateTransferStatusRequest $request, OfficeTransfer $officeTransfer)
    {
        try {
            $data = $request->validated();
            $transfer = $this->service->updateStatus($request->user(), $officeTransfer, $data['status'], $data['reason'] ?? null);
            return response()->json(['success' => true, 'message' => 'Office transfer status updated successfully.', 'data' => new OfficeTransferResource($transfer)]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
