<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /**
     * Display a listing of deliveries with pagination and filtering
     * GET /api/deliveries
     */
    public function index(Request $request)
    {
        $query = Delivery::with(['order:id,order_number,customer_name,total_amount', 'assignedBy:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by delivery method
        if ($request->has('delivery_method')) {
            $query->where('delivery_method', $request->delivery_method);
        }

        // Filter deliveries scheduled for today
        if ($request->boolean('today')) {
            $query->scheduledForToday();
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            })->orWhere('delivery_address', 'LIKE', "%{$search}%");
        }

        // Order by scheduled_at by default
        $query->orderBy('scheduled_at', 'desc');

        $deliveries = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $deliveries,
            'message' => 'Deliveries retrieved successfully'
        ]);
    }

    /**
     * Get deliveries scheduled for today
     * GET /api/deliveries/today
     */
    public function today()
    {
        $deliveries = Delivery::with(['order:id,order_number,customer_name,total_amount', 'assignedBy:id,name'])
            ->scheduledForToday()
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $deliveries,
            'count' => $deliveries->count(),
            'message' => 'Today\'s deliveries retrieved successfully'
        ]);
    }

    /**
     * Get delivery statistics for dashboard
     * GET /api/deliveries/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_deliveries' => Delivery::count(),
            'scheduled_today' => Delivery::scheduledForToday()->count(),
            'in_transit' => Delivery::byStatus(Delivery::STATUS_IN_TRANSIT)->count(),
            'delivered_today' => Delivery::byStatus(Delivery::STATUS_DELIVERED)
                ->whereDate('delivered_at', today())
                ->count(),
            'failed_deliveries' => Delivery::byStatus(Delivery::STATUS_FAILED)->count(),
            'pending_deliveries' => Delivery::whereIn('status', [
                Delivery::STATUS_SCHEDULED,
                Delivery::STATUS_IN_TRANSIT
            ])->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Delivery statistics retrieved successfully'
        ]);
    }

    /**
     * Store a newly created delivery
     * POST /api/deliveries
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'assigned_by' => 'required|exists:users,id',
            'delivery_method' => 'required|in:' . implode(',', [
                Delivery::METHOD_PICKUP,
                Delivery::METHOD_COURIER,
                Delivery::METHOD_DZUKA_DELIVERY
            ]),
            'delivery_address' => 'required|string|max:500',
            'delivery_fee' => 'required|numeric|min:0',
            'scheduled_at' => 'required|date|after:now',
            'delivery_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if order already has a delivery scheduled
        $existingDelivery = Delivery::where('order_id', $request->order_id)
            ->whereIn('status', [Delivery::STATUS_SCHEDULED, Delivery::STATUS_IN_TRANSIT])
            ->exists();

        if ($existingDelivery) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order already has a pending delivery'
            ], Response::HTTP_CONFLICT);
        }

        $deliveryData = $request->all();
        $deliveryData['status'] = Delivery::STATUS_SCHEDULED;

        $delivery = Delivery::create($deliveryData);

        // Load relationships for response
        $delivery->load(['order:id,order_number,customer_name,total_amount', 'assignedBy:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery scheduled successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified delivery
     * GET /api/deliveries/{id}
     */
    public function show($id)
    {
        $delivery = Delivery::with([
            'order' => function($query) {
                $query->select('id', 'order_number', 'customer_name', 'customer_phone',
                              'total_amount', 'status', 'created_at');
            },
            'assignedBy:id,name,email'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery details retrieved successfully'
        ]);
    }

    /**
     * Update the specified delivery
     * PUT /api/deliveries/{id}
     */
    public function update(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        // Prevent updating delivered or failed deliveries
        if (in_array($delivery->status, [Delivery::STATUS_DELIVERED, Delivery::STATUS_FAILED])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update completed or failed deliveries'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'assigned_by' => 'sometimes|exists:users,id',
            'delivery_method' => 'sometimes|in:' . implode(',', [
                Delivery::METHOD_PICKUP,
                Delivery::METHOD_COURIER,
                Delivery::METHOD_DZUKA_DELIVERY
            ]),
            'delivery_address' => 'sometimes|string|max:500',
            'delivery_fee' => 'sometimes|numeric|min:0',
            'scheduled_at' => 'sometimes|date|after:now',
            'delivery_notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:' . implode(',', [
                Delivery::STATUS_SCHEDULED,
                Delivery::STATUS_IN_TRANSIT,
                Delivery::STATUS_DELIVERED,
                Delivery::STATUS_FAILED
            ]),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delivery->update($request->all());

        // Load relationships for response
        $delivery->load(['order:id,order_number,customer_name,total_amount', 'assignedBy:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery updated successfully'
        ]);
    }

    /**
     * Mark delivery as in transit
     * PATCH /api/deliveries/{id}/mark-in-transit
     */
    public function markInTransit($id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status !== Delivery::STATUS_SCHEDULED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Can only mark scheduled deliveries as in transit'
            ], Response::HTTP_BAD_REQUEST);
        }

        $delivery->status = Delivery::STATUS_IN_TRANSIT;
        $delivery->save();

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery marked as in transit'
        ]);
    }

    /**
     * Mark delivery as delivered
     * PATCH /api/deliveries/{id}/mark-delivered
     */
    public function markDelivered(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'delivered_by' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delivery = Delivery::with('order')->findOrFail($id);

        if ($delivery->status === Delivery::STATUS_DELIVERED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery is already marked as delivered'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($delivery->status === Delivery::STATUS_FAILED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot mark failed delivery as delivered'
            ], Response::HTTP_BAD_REQUEST);
        }

        $delivery->markAsDelivered(
            $request->delivered_by,
            $request->delivery_notes
        );

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery marked as delivered successfully'
        ]);
    }

    /**
     * Mark delivery as failed
     * PATCH /api/deliveries/{id}/mark-failed
     */
    public function markFailed(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'delivery_notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delivery = Delivery::findOrFail($id);

        if ($delivery->status === Delivery::STATUS_DELIVERED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot mark delivered delivery as failed'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($delivery->status === Delivery::STATUS_FAILED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery is already marked as failed'
            ], Response::HTTP_BAD_REQUEST);
        }

        $delivery->status = Delivery::STATUS_FAILED;
        $delivery->delivery_notes = $request->delivery_notes;
        $delivery->save();

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery marked as failed'
        ]);
    }

    /**
     * Reschedule delivery
     * PATCH /api/deliveries/{id}/reschedule
     */
    public function reschedule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'delivery_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delivery = Delivery::findOrFail($id);

        if (in_array($delivery->status, [Delivery::STATUS_DELIVERED, Delivery::STATUS_FAILED])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot reschedule completed or failed deliveries'
            ], Response::HTTP_BAD_REQUEST);
        }

        $delivery->scheduled_at = $request->scheduled_at;
        $delivery->status = Delivery::STATUS_SCHEDULED;
        if ($request->has('delivery_notes')) {
            $delivery->delivery_notes = $request->delivery_notes;
        }
        $delivery->save();

        return response()->json([
            'status' => 'success',
            'data' => $delivery,
            'message' => 'Delivery rescheduled successfully'
        ]);
    }

    /**
     * Remove the specified delivery (cancel delivery)
     * DELETE /api/deliveries/{id}
     */
    public function destroy($id)
    {
        $delivery = Delivery::findOrFail($id);

        // Only allow deletion of scheduled deliveries
        if ($delivery->status !== Delivery::STATUS_SCHEDULED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Can only cancel scheduled deliveries'
            ], Response::HTTP_FORBIDDEN);
        }

        $delivery->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cancelled successfully'
        ]);
    }
}