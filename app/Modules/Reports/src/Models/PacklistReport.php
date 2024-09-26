<?php

namespace App\Modules\Reports\src\Models;

use App\Models\OrderProduct;

class PacklistReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Packlist';

        $this->fields = [
            'id' => 'order.id',
            'order_number' => 'order.order_number',
            'status' => 'order.status_code',
            'status_code' => 'order.status_code',
            'order_placed_at' => 'order.order_placed_at',
            'updated_at' => 'order.updated_at',
            'inventory_source_warehouse_id' => 'inventory_source.warehouse_id',
            'inventory_source_warehouse_code' => 'inventory_source.warehouse_code',
            'inventory_source_shelf_location' => 'inventory_source.shelve_location',
        ];

        $this->baseQuery = OrderProduct::query()
            ->where('quantity_to_ship', '>', 0)
            ->leftJoin('orders as order', 'orders_products.order_id', '=', 'order.id')
            ->leftJoin('inventory as inventory_source', function ($join) {
                $join->on('inventory_source.product_id', '=', 'orders_products.product_id');
            })
            ->leftJoin('products', 'products.id', '=', 'orders_products.product_id')
            ->whereNull('packed_at')
            ->whereNull('packer_user_id');
    }
}
