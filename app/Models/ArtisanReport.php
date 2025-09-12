<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtisanReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_id',
        'report_date',
        'orders_completed',
        'total_revenue',
        'total_commission',
        'material_costs',
        'overhead_costs',
        'net_profit',
        'ingredient_variance_cost'
    ];

    protected $casts = [
        'report_date' => 'date',
        'orders_completed' => 'integer',
        'total_revenue' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'material_costs' => 'decimal:2',
        'overhead_costs' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'ingredient_variance_cost' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function calculateNetProfit()
    {
        $this->net_profit = $this->total_commission - $this->ingredient_variance_cost;
        $this->save();
        return $this->net_profit;
    }

    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public static function generateReportForSupplier($supplierId, $date = null)
    {
        $date = $date ?: now()->format('Y-m-d');

        $orders = Order::where('supplier_id', $supplierId)
            ->whereDate('delivered_at', $date)
            ->where('status', Order::STATUS_DELIVERED)
            ->get();

        $report = new static([
            'supplier_id' => $supplierId,
            'report_date' => $date,
            'orders_completed' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'total_commission' => $orders->sum('artisan_commission'),
            'overhead_costs' => $orders->sum('overhead_amount'),
            'ingredient_variance_cost' => IngredientUsage::where('supplier_id', $supplierId)
                ->whereDate('recorded_at', $date)
                ->where('variance', '>', 0)
                ->sum('variance_cost'),
        ]);

        $report->calculateNetProfit();
        $report->save();

        return $report;
    }
}
