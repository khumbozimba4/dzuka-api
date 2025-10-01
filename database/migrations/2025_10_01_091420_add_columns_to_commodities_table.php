<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCommoditiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commodities', function (Blueprint $table) {
            // Direct Costs
            $table->decimal('raw_materials_cost', 10, 2)->nullable()->after('production_cost');
            $table->decimal('direct_labor_cost', 10, 2)->nullable()->after('raw_materials_cost');

            // Indirect Costs
            $table->decimal('utilities_cost', 10, 2)->nullable()->after('direct_labor_cost');
            $table->decimal('machinery_maintenance_cost', 10, 2)->nullable()->after('utilities_cost');
            $table->decimal('facility_rent_cost', 10, 2)->nullable()->after('machinery_maintenance_cost');
            $table->decimal('packaging_cost', 10, 2)->nullable()->after('facility_rent_cost');

            // Administrative and Sales Expenses
            $table->decimal('admin_support_cost', 10, 2)->nullable()->after('packaging_cost');
            $table->decimal('machinery_depreciation_cost', 10, 2)->nullable()->after('admin_support_cost');
            $table->decimal('marketing_cost', 10, 2)->nullable()->after('machinery_depreciation_cost');
            $table->decimal('rd_cost', 10, 2)->nullable()->after('marketing_cost');
            $table->decimal('market_testing_cost', 10, 2)->nullable()->after('rd_cost');
            $table->decimal('quality_assurance_cost', 10, 2)->nullable()->after('market_testing_cost');

            // Essential Additional Expenses
            $table->decimal('sustainability_cost', 10, 2)->nullable()->after('quality_assurance_cost');
            $table->decimal('staff_training_cost', 10, 2)->nullable()->after('sustainability_cost');
            $table->decimal('insurance_cost', 10, 2)->nullable()->after('staff_training_cost');
        });
    }

    public function down()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->dropColumn([
                'raw_materials_cost',
                'direct_labor_cost',
                'utilities_cost',
                'machinery_maintenance_cost',
                'facility_rent_cost',
                'packaging_cost',
                'admin_support_cost',
                'machinery_depreciation_cost',
                'marketing_cost',
                'rd_cost',
                'market_testing_cost',
                'quality_assurance_cost',
                'sustainability_cost',
                'staff_training_cost',
                'insurance_cost',
            ]);
        });
    }

}
