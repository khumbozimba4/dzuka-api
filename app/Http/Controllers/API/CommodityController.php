<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommodityController extends Controller
{
    /**
     * Display paginated listing of active commodities
     * GET /api/commodities
     */
    public function index(Request $request)
    {
        $query = Commodity::with(['sector', 'ingredients'])
            ->active()
            ->orderBy('name');

        // Filter by sector if provided
        if ($request->has('sector_id')) {
            $query->bySector($request->sector_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $commodities = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $commodities,
            'message' => 'Commodities retrieved successfully'
        ]);
    }

    /**
     * Get commodities grouped by sectors for catalogue display
     * GET /api/commodities/catalogue
     */
    public function catalogue()
    {
        $sectors = Sector::with(['commodities' => function($query) {
            $query->active()
                  ->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
                  ->orderBy('name');
        }])
        ->whereHas('commodities', function($query) {
            $query->active();
        })
        ->orderBy('name')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Product catalogue retrieved successfully'
        ]);
    }

    /**
     * Get commodities by specific sector
     * GET /api/commodities/sector/{sectorId}
     */
    public function getBySector($sectorId)
    {
        $sector = Sector::findOrFail($sectorId);

        $commodities = Commodity::with('ingredients')
            ->active()
            ->bySector($sectorId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sector' => $sector,
                'commodities' => $commodities
            ],
            'message' => "Commodities for {$sector->name} sector retrieved successfully"
        ]);
    }

    /**
     * Store a newly created commodity (Admin only)
     * POST /api/commodities
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'selling_price' => 'required|numeric|min:0',
            'production_time_hours' => 'required|integer|min:1',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.cost_per_unit' => 'required_with:ingredients|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commodityData = $request->only([
            'sector_id', 'name', 'description', 'selling_price', 'production_time_hours'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('commodities', 'public');
            $commodityData['image'] = $imagePath;
        }

        $commodity = Commodity::create($commodityData);

        // Attach ingredients if provided
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredient) {
                $commodity->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity_required' => $ingredient['quantity_required'],
                    'cost_per_unit' => $ingredient['cost_per_unit']
                ]);
            }
        }

        // Calculate production cost
        $commodity->calculateProductionCost();

        $commodity->load(['sector', 'ingredients']);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified commodity with full details
     * GET /api/commodities/{id}
     */
    public function show($id)
    {
        $commodity = Commodity::with(['sector', 'ingredients'])
            ->findOrFail($id);

        // Add calculated fields
        $commodity->profit_margin = $commodity->profit_margin;

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity details retrieved successfully'
        ]);
    }

    /**
     * Get commodity details for customer view (limited info)
     * GET /api/commodities/{id}/customer
     */
    public function showForCustomer($id)
    {
        $commodity = Commodity::with('sector')
            ->active()
            ->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity details retrieved successfully'
        ]);
    }

    /**
     * Update the specified commodity (Admin only)
     * PUT /api/commodities/{id}
     */
    public function update(Request $request, $id)
    {
        $commodity = Commodity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sector_id' => 'sometimes|exists:sectors,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'selling_price' => 'sometimes|numeric|min:0',
            'production_time_hours' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.cost_per_unit' => 'required_with:ingredients|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only([
            'sector_id', 'name', 'description', 'selling_price', 'production_time_hours', 'is_active'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($commodity->image) {
                Storage::disk('public')->delete($commodity->image);
            }
            $imagePath = $request->file('image')->store('commodities', 'public');
            $updateData['image'] = $imagePath;
        }

        $commodity->update($updateData);

        // Update ingredients if provided
        if ($request->has('ingredients')) {
            $commodity->ingredients()->detach();
            foreach ($request->ingredients as $ingredient) {
                $commodity->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity_required' => $ingredient['quantity_required'],
                    'cost_per_unit' => $ingredient['cost_per_unit']
                ]);
            }
            // Recalculate production cost
            $commodity->calculateProductionCost();
        }

        $commodity->load(['sector', 'ingredients']);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity updated successfully'
        ]);
    }

    /**
     * Toggle commodity active status (Admin only)
     * PATCH /api/commodities/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $commodity = Commodity::findOrFail($id);
        $commodity->is_active = !$commodity->is_active;
        $commodity->save();

        $status = $commodity->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => "Commodity {$status} successfully"
        ]);
    }

    /**
     * Get popular/featured commodities
     * GET /api/commodities/featured
     */
    public function featured()
    {
        // This could be based on order frequency, rating, or manual selection
        // For now, we'll get random active commodities
        $commodities = Commodity::with('sector')
            ->active()
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $commodities,
            'message' => 'Featured commodities retrieved successfully'
        ]);
    }

    /**
     * Get commodity statistics for admin dashboard
     * GET /api/commodities/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_commodities' => Commodity::count(),
            'active_commodities' => Commodity::active()->count(),
            'inactive_commodities' => Commodity::where('is_active', false)->count(),
            'commodities_by_sector' => Commodity::join('sectors', 'commodities.sector_id', '=', 'sectors.id')
                ->selectRaw('sectors.name as sector_name, COUNT(*) as count')
                ->groupBy('sectors.id', 'sectors.name')
                ->get(),
            'average_selling_price' => Commodity::active()->avg('selling_price'),
            'average_production_cost' => Commodity::active()->avg('production_cost'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Commodity statistics retrieved successfully'
        ]);
    }

    /**
     * Remove the specified commodity (Soft delete - set inactive)
     * DELETE /api/commodities/{id}
     */
    public function destroy($id)
    {
        $commodity = Commodity::findOrFail($id);

        // Instead of hard delete, set as inactive
        $commodity->is_active = false;
        $commodity->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Commodity deactivated successfully'
        ]);
    }
}