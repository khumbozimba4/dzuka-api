<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SectorController extends Controller
{
    /**
     * Display a listing of sectors with pagination and filtering
     * GET /api/sectors
     */
    public function index(Request $request)
    {
        $query = Sector::query();

        // Filter by active status
        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Include products count if requested
        if ($request->boolean('with_products_count')) {
            $query->withCount(['products', 'activeProducts']);
        }

        // Include products if requested (for catalogue view)
        if ($request->boolean('with_products')) {
            $query->with(['activeProducts' => function($q) {
                $q->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
                  ->orderBy('name');
            }]);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Order by name by default
        $query->orderBy('name');

        $sectors = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sectors retrieved successfully'
        ]);
    }

    /**
     * Get sectors for dropdown/selection (simplified data)
     * GET /api/sectors/list
     */
    public function list()
    {
        $sectors = Sector::active()
            ->select('id', 'name', 'description')
            ->withCount('activeProducts')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sector list retrieved successfully'
        ]);
    }

    /**
     * Get active sectors with their products for public catalogue
     * GET /api/sectors/catalogue
     */
    public function catalogue()
    {
        $sectors = Sector::active()
            ->with(['activeProducts' => function($query) {
                $query->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
                      ->orderBy('name');
            }])
            ->whereHas('activeProducts')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sector catalogue retrieved successfully'
        ]);
    }

    /**
     * Get sectors with product statistics for dashboard
     * GET /api/sectors/dashboard
     */
    public function dashboard()
    {
        $sectors = Sector::withCount(['products', 'activeProducts'])
            ->orderBy('name')
            ->get()
            ->map(function ($sector) {
                return [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'description' => $sector->description,
                    'image' => $sector->image,
                    'is_active' => $sector->is_active,
                    'total_products' => $sector->products_count,
                    'active_products' => $sector->active_products_count,
                    'inactive_products' => $sector->products_count - $sector->active_products_count
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sector dashboard data retrieved successfully'
        ]);
    }

    /**
     * Store a newly created sector (Admin only)
     * POST /api/sectors
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sectors,name',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sectorData = $request->only(['name', 'description']);
        $sectorData['is_active'] = true; // New sectors are active by default

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sectors', 'public');
            $sectorData['image'] = $imagePath;
        }

        $sector = Sector::create($sectorData);

        return response()->json([
            'status' => 'success',
            'data' => $sector,
            'message' => 'Sector created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified sector with products
     * GET /api/sectors/{id}
     */
    public function show($id, Request $request)
    {
        $query = Sector::where('id', $id);

        // Include products if requested
        if ($request->boolean('with_products')) {
            $query->with(['products' => function($q) use ($request) {
                if ($request->boolean('active_only')) {
                    $q->where('is_active', true);
                }
                $q->orderBy('name');
            }]);
        }

        // Include products count
        $query->withCount(['products', 'activeProducts']);

        $sector = $query->firstOrFail();

        // Add calculated statistics
        $sector->products_statistics = [
            'total_products' => $sector->products_count,
            'active_products' => $sector->active_products_count,
            'inactive_products' => $sector->products_count - $sector->active_products_count
        ];

        return response()->json([
            'status' => 'success',
            'data' => $sector,
            'message' => 'Sector details retrieved successfully'
        ]);
    }

    /**
     * Get sector with products for customer view (public)
     * GET /api/sectors/{id}/public
     */
    public function showPublic($id)
    {
        $sector = Sector::active()
            ->with(['activeProducts' => function($query) {
                $query->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
                      ->orderBy('name');
            }])
            ->withCount('activeProducts')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $sector,
            'message' => 'Sector information retrieved successfully'
        ]);
    }

    /**
     * Update the specified sector (Admin only)
     * PUT /api/sectors/{id}
     */
    public function update(Request $request, $id)
    {
        $sector = Sector::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:sectors,name,' . $id,
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only(['name', 'description', 'is_active']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($sector->image) {
                Storage::disk('public')->delete($sector->image);
            }
            $imagePath = $request->file('image')->store('sectors', 'public');
            $updateData['image'] = $imagePath;
        }

        $sector->update($updateData);

        return response()->json([
            'status' => 'success',
            'data' => $sector,
            'message' => 'Sector updated successfully'
        ]);
    }

    /**
     * Toggle sector active status (Admin only)
     * PATCH /api/sectors/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $sector = Sector::findOrFail($id);
        $sector->is_active = !$sector->is_active;
        $sector->save();

        $status = $sector->is_active ? 'activated' : 'deactivated';

        // If deactivating sector, optionally deactivate all its products
        if (!$sector->is_active) {
            $affectedProducts = $sector->products()->where('is_active', true)->count();
            $sector->products()->update(['is_active' => false]);

            return response()->json([
                'status' => 'success',
                'data' => $sector,
                'message' => "Sector {$status} successfully. {$affectedProducts} products also deactivated."
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sector,
            'message' => "Sector {$status} successfully"
        ]);
    }

    /**
     * Get sector statistics for admin dashboard
     * GET /api/sectors/statistics
     */
    public function statistics()
    {
        $totalSectors = Sector::count();
        $activeSectors = Sector::active()->count();

        $stats = [
            'total_sectors' => $totalSectors,
            'active_sectors' => $activeSectors,
            'inactive_sectors' => $totalSectors - $activeSectors,
            'sectors_with_products' => Sector::has('products')->count(),
            'sectors_with_active_products' => Sector::has('activeProducts')->count(),
            'average_products_per_sector' => Sector::withCount('products')->avg('products_count') ?? 0,
            'sector_product_distribution' => Sector::withCount(['products', 'activeProducts'])
                ->get(['id', 'name', 'products_count', 'active_products_count'])
                ->map(function ($sector) {
                    return [
                        'sector_name' => $sector->name,
                        'total_products' => $sector->products_count,
                        'active_products' => $sector->active_products_count
                    ];
                })
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Sector statistics retrieved successfully'
        ]);
    }

    /**
     * Get sectors ready for product assignment (have less than maximum products)
     * GET /api/sectors/available-for-products
     */
    public function availableForProducts()
    {
        $maxProductsPerSector = 15; // As per your requirements

        $sectors = Sector::active()
            ->withCount('products')
            ->having('products_count', '<', $maxProductsPerSector)
            ->orderBy('products_count')
            ->orderBy('name')
            ->get()
            ->map(function ($sector) use ($maxProductsPerSector) {
                return [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'current_products' => $sector->products_count,
                    'max_products' => $maxProductsPerSector,
                    'available_slots' => $maxProductsPerSector - $sector->products_count
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sectors available for new products retrieved successfully'
        ]);
    }

    /**
     * Validate sector capacity before adding products
     * GET /api/sectors/{id}/capacity-check
     */
    public function checkCapacity($id)
    {
        $sector = Sector::withCount('products')->findOrFail($id);
        $maxProducts = 15;
        $minProducts = 5;
        $currentProducts = $sector->products_count;

        $status = 'adequate';
        if ($currentProducts < $minProducts) {
            $status = 'below_minimum';
        } elseif ($currentProducts >= $maxProducts) {
            $status = 'at_maximum';
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'sector' => $sector->only(['id', 'name', 'is_active']),
                'capacity' => [
                    'current_products' => $currentProducts,
                    'minimum_required' => $minProducts,
                    'maximum_allowed' => $maxProducts,
                    'available_slots' => max(0, $maxProducts - $currentProducts),
                    'status' => $status,
                    'can_add_products' => $currentProducts < $maxProducts,
                    'needs_more_products' => $currentProducts < $minProducts
                ]
            ],
            'message' => 'Sector capacity information retrieved successfully'
        ]);
    }

    /**
     * Remove the specified sector (Soft delete - set inactive)
     * DELETE /api/sectors/{id}
     */
    public function destroy($id)
    {
        $sector = Sector::findOrFail($id);

        // Check if sector has products
        $productCount = $sector->products()->count();

        if ($productCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot delete sector. It has {$productCount} products. Please remove or reassign products first."
            ], Response::HTTP_CONFLICT);
        }

        // Instead of hard delete, set as inactive
        $sector->is_active = false;
        $sector->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Sector deactivated successfully'
        ]);
    }

    /**
     * Bulk operations for sectors
     * POST /api/sectors/bulk-action
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'sector_ids' => 'required|array|min:1',
            'sector_ids.*' => 'exists:sectors,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sectors = Sector::whereIn('id', $request->sector_ids);
        $action = $request->action;
        $affectedCount = 0;

        switch ($action) {
            case 'activate':
                $affectedCount = $sectors->update(['is_active' => true]);
                $message = "{$affectedCount} sectors activated successfully";
                break;

            case 'deactivate':
                $affectedCount = $sectors->update(['is_active' => false]);
                // Also deactivate products in these sectors
                $productCount = Sector::whereIn('id', $request->sector_ids)
                    ->with('products')
                    ->get()
                    ->sum(function($sector) {
                        return $sector->products()->update(['is_active' => false]);
                    });
                $message = "{$affectedCount} sectors and {$productCount} products deactivated successfully";
                break;

            case 'delete':
                // Check if any sector has products
                $sectorsWithProducts = $sectors->has('products')->count();
                if ($sectorsWithProducts > 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "{$sectorsWithProducts} sectors have products and cannot be deleted"
                    ], Response::HTTP_CONFLICT);
                }
                $affectedCount = $sectors->update(['is_active' => false]);
                $message = "{$affectedCount} sectors deactivated successfully";
                break;
        }

        return response()->json([
            'status' => 'success',
            'data' => ['affected_count' => $affectedCount],
            'message' => $message
        ]);
    }
}