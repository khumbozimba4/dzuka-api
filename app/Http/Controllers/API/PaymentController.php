<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Payment::with(['order', 'customer'])->latest()->get(),
            'message' => 'Payment retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'customer_id' => 'required|exists:users,id',
            'payment_type' => 'required|in:deposit,balance',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,card',
            'payment_details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $data['payment_reference'] = Payment::generatePaymentReference();
        $data['status'] = Payment::STATUS_PENDING;

        $payment = Payment::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $payment,
            'message' => 'Payment created successfully'
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $payment = Payment::with(['order', 'customer'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'payment_type' => 'in:deposit,balance',
            'amount' => 'numeric|min:0.01',
            'payment_method' => 'in:cash,bank_transfer,mobile_money,card',
            'payment_details' => 'nullable|array',
            'status' => 'in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payment->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'data' => $payment,
            'message' => 'Payment updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }

    // 🔹 Custom Methods

    public function getByOrder($orderId)
    {
        $payments = Payment::where('order_id', $orderId)->with('customer')->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    public function markCompleted($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->markAsCompleted();

        return response()->json([
            'status' => 'success',
            'data' => $payment,
            'message' => 'Payment marked as completed'
        ]);
    }

    public function refund($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->status = Payment::STATUS_REFUNDED;
        $payment->save();

        return response()->json([
            'status' => 'success',
            'data' => $payment,
            'message' => 'Payment refunded successfully'
        ]);
    }
}
