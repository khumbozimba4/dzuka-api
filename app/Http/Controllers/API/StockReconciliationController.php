<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockReconciliation;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StockReconciliationController extends Controller
{
    /**
     * Display a listing of stock reconciliations
     * GET /api/stock-reconciliations
     */
    public function index(Request $request)
    {
        $query = StockReconciliation::with(['ingredient:id,name,unit_of_measurement,current_stock', 'reconciledBy:id,name']);

        // Filter by ingredient
        if ($request->has('ingredient_id')) {
            $query->where('ingredient_id', $request->ingredient_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('reconciliation_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('reconciliation_date', '<=', $request->date_to);
        }

        // Filter by variance
        if ($request->boolean('has_variance')) {
            $query->whereRaw('variance != 0');
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('ingredient', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Order by most recent first
        $query->orderBy('reconciliation_date', 'desc');

        $reconciliations = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $reconciliations,
            'message' => 'Stock reconciliations retrieved successfully'
        ]);
    }

    /**
     * Get reconciliations for a specific ingredient
     * GET /api/ingredients/{ingredientId}/reconciliations
     */
    public function ingredientReconciliations($ingredientId, Request $request)
    {
        $ingredient = Ingredient::findOrFail($ingredientId);

        $query = StockReconciliation::where('ingredient_id', $ingredientId)
            ->with(['reconciledBy:id,name']);

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('reconciliation_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('reconciliation_date', '<=', $request->date_to);
        }

        $query->orderBy('reconciliation_date', 'desc');

        $reconciliations = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => [
                'ingredient' => $ingredient,
                'reconciliations' => $reconciliations
            ],
            'message' => 'Ingredient reconciliations retrieved successfully'
        ]);
    }

    /**
     * Store a newly created reconciliation
     * POST /api/stock-reconciliations
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredient_id' => 'required|exists:ingredients,id',
            'system_stock' => 'required|numeric|min:0',
            'physical_stock' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'reconciliation_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ingredient = Ingredient::findOrFail($request->ingredient_id);

        // Calculate variance and variance value
        $variance = $request->physical_stock - $request->system_stock;
        $variance_value = $variance * $request->cost_per_unit;

        $reconciliation = StockReconciliation::create([
            'ingredient_id' => $request->ingredient_id,
            'system_stock' => $request->system_stock,
            'physical_stock' => $request->physical_stock,
            'variance' => $variance,
            'cost_per_unit' => $request->cost_per_unit,
            'variance_value' => $variance_value,
            'notes' => $request->notes,
            'reconciled_by' => Auth::id(),
            'reconciliation_date' => $request->reconciliation_date ?? now()
        ]);

        // Update ingredient stock to physical count
        $ingredient->current_stock = $request->physical_stock;
        $ingredient->save();

        $reconciliation->load(['ingredient:id,name,unit_of_measurement,current_stock', 'reconciledBy:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $reconciliation,
            'message' => 'Stock reconciliation created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified reconciliation
     * GET /api/stock-reconciliations/{id}
     */
    public function show($id)
    {
        $reconciliation = StockReconciliation::with([
            'ingredient:id,name,unit_of_measurement,current_stock,cost_per_unit',
            'reconciledBy:id,name,email'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $reconciliation,
            'message' => 'Reconciliation details retrieved successfully'
        ]);
    }

    /**
     * Update the specified reconciliation
     * PUT /api/stock-reconciliations/{id}
     */
    public function update(Request $request, $id)
    {
        $reconciliation = StockReconciliation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ingredient_id' => 'sometimes|exists:ingredients,id',
            'system_stock' => 'sometimes|numeric|min:0',
            'physical_stock' => 'sometimes|numeric|min:0',
            'cost_per_unit' => 'sometimes|numeric|min:0',
            'reconciliation_date' => 'sometimes|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Recalculate variance if stock values change
        if ($request->has('system_stock') || $request->has('physical_stock') || $request->has('cost_per_unit')) {
            $system_stock = $request->get('system_stock', $reconciliation->system_stock);
            $physical_stock = $request->get('physical_stock', $reconciliation->physical_stock);
            $cost_per_unit = $request->get('cost_per_unit', $reconciliation->cost_per_unit);

            $variance = $physical_stock - $system_stock;
            $variance_value = $variance * $cost_per_unit;

            $reconciliation->variance = $variance;
            $reconciliation->variance_value = $variance_value;

            // Update ingredient stock if physical stock changed
            if ($request->has('physical_stock')) {
                $ingredient = Ingredient::find($reconciliation->ingredient_id);
                $ingredient->current_stock = $physical_stock;
                $ingredient->save();
            }
        }

        // Update other fields
        if ($request->has('ingredient_id')) {
            $reconciliation->ingredient_id = $request->ingredient_id;
        }
        if ($request->has('system_stock')) {
            $reconciliation->system_stock = $request->system_stock;
        }
        if ($request->has('physical_stock')) {
            $reconciliation->physical_stock = $request->physical_stock;
        }
        if ($request->has('cost_per_unit')) {
            $reconciliation->cost_per_unit = $request->cost_per_unit;
        }
        if ($request->has('reconciliation_date')) {
            $reconciliation->reconciliation_date = $request->reconciliation_date;
        }
        if ($request->has('notes')) {
            $reconciliation->notes = $request->notes;
        }

        $reconciliation->save();

        $reconciliation->load(['ingredient:id,name,unit_of_measurement,current_stock', 'reconciledBy:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => $reconciliation,
            'message' => 'Reconciliation updated successfully'
        ]);
    }

    /**
     * Get reconciliation statistics
     * GET /api/stock-reconciliations/statistics
     */
    public function statistics()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        $stats = [
            'total_reconciliations' => StockReconciliation::count(),
            'reconciliations_this_month' => StockReconciliation::where('reconciliation_date', '>=', $startOfMonth)->count(),
            'total_variance_value' => StockReconciliation::sum('variance_value') ?? 0,
            'average_variance' => StockReconciliation::selectRaw('AVG(ABS(variance)) as average')
                ->first()
                ->average ?? 0,
            'ingredients_with_variances' => StockReconciliation::where('variance', '!=', 0)
                ->distinct('ingredient_id')
                ->count('ingredient_id'),
            'recent_reconciliations' => StockReconciliation::with(['ingredient:id,name,unit_of_measurement', 'reconciledBy:id,name'])
                ->orderBy('reconciliation_date', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Reconciliation statistics retrieved successfully'
        ]);
    }

    /**
     * Get reconciliation trends/analytics
     * GET /api/stock-reconciliations/analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 30); // days

        $analytics = [
            'top_ingredients_with_variances' => StockReconciliation::with('ingredient:id,name,unit_of_measurement')
                ->where('variance', '!=', 0)
                ->where('reconciliation_date', '>=', now()->subDays($period))
                ->selectRaw('ingredient_id, COUNT(*) as count, SUM(ABS(variance)) as total_variance, SUM(ABS(variance_value)) as total_value')
                ->groupBy('ingredient_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),

            'reconciliations_by_day' => StockReconciliation::where('reconciliation_date', '>=', now()->subDays($period))
                ->selectRaw('DATE(reconciliation_date) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            'surplus_vs_shortage' => [
                'surplus' => StockReconciliation::where('variance', '>', 0)
                    ->where('reconciliation_date', '>=', now()->subDays($period))
                    ->count(),
                'shortage' => StockReconciliation::where('variance', '<', 0)
                    ->where('reconciliation_date', '>=', now()->subDays($period))
                    ->count(),
                'exact_match' => StockReconciliation::where('variance', '=', 0)
                    ->where('reconciliation_date', '>=', now()->subDays($period))
                    ->count()
            ],

            'total_variance_value' => [
                'surplus_value' => StockReconciliation::where('variance', '>', 0)
                    ->where('reconciliation_date', '>=', now()->subDays($period))
                    ->sum('variance_value') ?? 0,
                'shortage_value' => abs(StockReconciliation::where('variance', '<', 0)
                    ->where('reconciliation_date', '>=', now()->subDays($period))
                    ->sum('variance_value') ?? 0)
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $analytics,
            'message' => 'Reconciliation analytics retrieved successfully'
        ]);
    }

    /**
     * Remove the specified reconciliation
     * DELETE /api/stock-reconciliations/{id}
     */
    public function destroy($id)
    {
        $reconciliation = StockReconciliation::findOrFail($id);

        // Note: Consider reversing the stock adjustment
        // Restore previous stock level by reversing the variance
        $ingredient = $reconciliation->ingredient;
        if ($ingredient) {
            $ingredient->current_stock = $reconciliation->system_stock;
            $ingredient->save();
        }

        $reconciliation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reconciliation deleted successfully'
        ]);
    }
}