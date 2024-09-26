<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory';

        $this->baseQuery = Inventory::query()
            ->leftJoin('products as product', 'inventory.product_id', '=', 'product.id')
            ->leftJoin('products_prices as product_prices', function ($join) {
                $join->on('inventory.product_id', '=', 'product_prices.product_id')
                    ->on('inventory.warehouse_id', '=', 'product_prices.warehouse_id');
            })
            ->leftJoin('inventory_movements_statistics', function ($join) {
                $join->on('inventory.id', '=', 'inventory_movements_statistics.inventory_id')
                    ->on('inventory.product_id', '=', 'inventory_movements_statistics.product_id')
                    ->on('inventory.warehouse_code', '=', 'inventory_movements_statistics.warehouse_code')
                    ->where('inventory_movements_statistics.type', '=', 'sale');
            });

        $this->addField('product_sku', 'product.sku', hidden: false);
        $this->addField('product_name', 'product.name', hidden: false);
        $this->addField('quantity', 'inventory.quantity', 'float', hidden: false);
        $this->addField('quantity_reserved', 'inventory.quantity_reserved', 'float', hidden: false);
        $this->addField('quantity_available', 'inventory.quantity_available', 'float', hidden: false);
        $this->addField('quantity_incoming', 'inventory.quantity_incoming', 'float', hidden: false);
        $this->addField('quantity_required', 'inventory.quantity_required', 'float', hidden: false);
        $this->addField('reorder_point', 'inventory.reorder_point', 'float');
        $this->addField('restock_level', 'inventory.restock_level', 'float');

        $this->addField('unit_price', 'product_prices.price', 'float');
        $this->addField('unit_cost', 'product_prices.cost', 'float');
        $this->addField('total_price', DB::raw('ROUND(product_prices.price * inventory.quantity, 2)'), 'float');
        $this->addField('total_cost', DB::raw('ROUND(product_prices.cost * inventory.quantity, 2)'), 'float');

        $this->addField('sale_price', 'product_prices.sale_price', 'float');
        $this->addField('sale_start_date', 'product_prices.sale_price_start_date', 'datetime');
        $this->addField('sale_end_date', 'product_prices.sale_price_end_date', 'datetime');

        $this->addField('supplier', 'product.supplier');
        $this->addField('department', 'product.department');
        $this->addField('category', 'product.category');
        $this->addField('shelf_location', 'inventory.shelve_location');

        $this->addField('reservations', DB::raw('SELECT GROUP_CONCAT(concat(quantity_reserved, \' - \', comment) SEPARATOR \', \') FROM `inventory_reservations` WHERE inventory_reservations.inventory_id = inventory.id'), 'string');

        $this->addField('first_movement_at', 'inventory.first_movement_at', 'datetime');
        $this->addField('last_movement_at', 'inventory.last_movement_at', 'datetime');
        $this->addField('first_received_at', 'inventory.first_received_at', 'datetime');
        $this->addField('last_received_at', 'inventory.last_received_at', 'datetime');
        $this->addField('first_sold_at', 'inventory.first_sold_at', 'datetime');
        $this->addField('last_sold_at', 'inventory.last_sold_at', 'datetime');
        $this->addField('first_counted_at', 'inventory.first_counted_at', 'datetime');
        $this->addField('last_counted_at', 'inventory.last_counted_at', 'datetime');
        $this->addField('last_movement_id', 'inventory.last_movement_id', 'datetime');
        $this->addField('deleted_at', 'inventory.deleted_at', 'datetime');
        $this->addField('created_at', 'inventory.created_at', 'datetime');
        $this->addField('updated_at', 'inventory.updated_at', 'datetime');

        $this->addField('last_sequence_number', 'inventory.last_sequence_number', 'integer');
        $this->addField('recount_required', 'inventory.recount_required');
        $this->addField('product_id', 'inventory.product_id', 'integer');
        $this->addField('id', 'inventory.id', 'integer');
        $this->addField('warehouse_id', 'inventory.warehouse_id', 'integer');

        $this->addField('warehouse_code', 'inventory.warehouse_code', hidden: false);

        $this->addField('last_7_days_sales', DB::raw('COALESCE(inventory_movements_statistics.last7days_quantity_delta, 0) * -1'), hidden: false);
        $this->addField('last_14_days_sales', DB::raw('COALESCE(inventory_movements_statistics.last14days_quantity_delta, 0) * -1'), hidden: false);
        $this->addField('last_28_days_sales', DB::raw('COALESCE(inventory_movements_statistics.last28days_quantity_delta, 0) * -1'), hidden: false);

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
