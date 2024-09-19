<?php

namespace App\Modules\Reports\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryDashboardRecords extends Model
{
    use SoftDeletes;

    protected $table = 'modules_reports_inventory_dashboard_records';

    protected $fillable = [
        'warehouse_id',
        'warehouse_code',
        'missing_restock_levels',
        'wh_products_available',
        'wh_products_out_of_stock',
        'wh_products_required',
        'wh_products_incoming',
        'wh_products_stock_level_ok',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
