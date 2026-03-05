<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WarehouseTransfer;
use App\Models\Warehouse;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WarehouseTransferController extends Controller
{
    /**
     * Display a listing of warehouse transfers
     * GET /api/warehouse-transfers
     */
    public function index(Request $request)
    {
        $query = WarehouseTransfer::with([
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'ingredient:id,name,unit_of_measurement',
            'initiator:id,name',
            'approver:id,name',
            'receiver:id,name'
        ]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where(function($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by ingredient
        if ($request->has('ingredient_id')) {
            $query->where('ingredient_id', $request->ingredient_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('transfer_number', 'LIKE', "%{$search}%");
        }

        // Order by most recent
        $query->orderBy('created_at', 'desc');

        $transfers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $transfers,
            'message' => 'Warehouse transfers retrieved successfully'
        ]);
    }

    /**
     * Store a newly created transfer
     * POST /api/warehouse-transfers
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Verify source warehouse has sufficient stock
        $fromWarehouse = Warehouse::findOrFail($request->from_warehouse_id);
        if (!$fromWarehouse->hasSufficientStock($request->ingredient_id, $request->quantity)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock in source warehouse'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $transfer = WarehouseTransfer::create([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'ingredient_id' => $request->ingredient_id,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'initiated_by' => Auth::id(),
                'notes' => $request->notes,
                'initiated_at' => now()
            ]);

            DB::commit();

            $transfer->load([
                'fromWarehouse:id,name,code',
                'toWarehouse:id,name,code',
                'ingredient:id,name,unit_of_measurement'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transfer,
                'message' => 'Transfer initiated successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate transfer: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified transfer
     * GET /api/warehouse-transfers/{id}
     */
    public function show($id)
    {
        $transfer = WarehouseTransfer::with([
            'fromWarehouse',
            'toWarehouse',
            'ingredient',
            'initiator',
            'approver',
            'receiver'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transfer,
            'message' => 'Transfer details retrieved successfully'
        ]);
    }

    /**
     * Approve a transfer (moves to in_transit status)
     * POST /api/warehouse-transfers/{id}/approve
     */
    public function approve($id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending transfers can be approved'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify source warehouse still has sufficient stock
        $fromWarehouse = Warehouse::findOrFail($transfer->from_warehouse_id);
        if (!$fromWarehouse->hasSufficientStock($transfer->ingredient_id, $transfer->quantity)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock in source warehouse'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Reduce stock from source warehouse
            $ingredient = Ingredient::findOrFail($transfer->ingredient_id);
            $ingredient->reduceStockFromWarehouse($transfer->from_warehouse_id, $transfer->quantity);

            // Update transfer status
            $transfer->update([
                'status' => 'in_transit',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            DB::commit();

            $transfer->load([
                'fromWarehouse',
                'toWarehouse',
                'ingredient',
                'approver'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transfer,
                'message' => 'Transfer approved and stock deducted from source warehouse'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve transfer: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete a transfer (adds stock to destination)
     * POST /api/warehouse-transfers/{id}/complete
     */
    public function complete($id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);

        if ($transfer->status !== 'in_transit') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only in-transit transfers can be completed'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Add stock to destination warehouse
            $ingredient = Ingredient::findOrFail($transfer->ingredient_id);
            $ingredient->addStockToWarehouse($transfer->to_warehouse_id, $transfer->quantity);

            // Update transfer status
            $transfer->update([
                'status' => 'completed',
                'received_by' => Auth::id(),
                'completed_at' => now()
            ]);

            DB::commit();

            $transfer->load([
                'fromWarehouse',
                'toWarehouse',
                'ingredient',
                'receiver'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transfer,
                'message' => 'Transfer completed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete transfer: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel a transfer
     * POST /api/warehouse-transfers/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);

        if ($transfer->status === 'completed' || $transfer->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot cancel completed or already cancelled transfers'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // If transfer was in_transit, return stock to source warehouse
            if ($transfer->status === 'in_transit') {
                $ingredient = Ingredient::findOrFail($transfer->ingredient_id);
                $ingredient->addStockToWarehouse($transfer->from_warehouse_id, $transfer->quantity);
            }

            $transfer->update([
                'status' => 'cancelled',
                'notes' => ($transfer->notes ? $transfer->notes . "\n\n" : '') .
                          "Cancelled by user on " . now()->toDateTimeString() .
                          ($request->cancellation_reason ? ": " . $request->cancellation_reason : "")
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $transfer,
                'message' => 'Transfer cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel transfer: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get transfer statistics
     * GET /api/warehouse-transfers/statistics
     */
    public function statistics(Request $request)
    {
        $query = WarehouseTransfer::query();

        // Optional warehouse filter
        if ($request->has('warehouse_id')) {
            $query->where(function($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }

        $stats = [
            'total_transfers' => $query->count(),
            'pending_transfers' => (clone $query)->pending()->count(),
            'in_transit_transfers' => (clone $query)->inTransit()->count(),
            'completed_transfers' => (clone $query)->completed()->count(),
            'cancelled_transfers' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Transfer statistics retrieved successfully'
        ]);
    }
}