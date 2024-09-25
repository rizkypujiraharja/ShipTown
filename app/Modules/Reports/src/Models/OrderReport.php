<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Order';

        $this->view = 'report-order';

        $this->baseQuery = Order::query()
            ->leftJoin('orders_addresses as order_addresses_shipping', 'orders.shipping_address_id', '=', 'order_addresses_shipping.id')
            ->leftJoin('orders_addresses as order_addresses_billing', 'orders.billing_address_id', '=', 'order_addresses_billing.id')
            ->leftJoin('orders_products_totals', 'orders.id', 'orders_products_totals.order_id');

        $this->addField('order_number', 'orders.order_number', 'number', hidden: false);
        $this->addField('status_code', 'orders.status_code', hidden: false);
        $this->addField('order_placed_at', 'orders.order_placed_at', 'datetime', hidden: false);
        $this->addField('order_closed_at', 'orders.order_closed_at', 'datetime', hidden: true);

        $this->addField('total_product', 'orders.total_products', 'float', hidden: false);
        $this->addField('total_discounts', 'orders.total_discounts', 'float', hidden: false);
        $this->addField('total_shipping_cost', 'orders.total_shipping', 'float', hidden: false);
        $this->addField('total_order', 'orders.total_order', 'float', hidden: false);
        $this->addField('total_paid', 'orders.total_paid', 'float', hidden: false);
        $this->addField('total_outstanding', 'orders.total_outstanding', 'float', hidden: true);

        $this->addField('count_product', 'orders_products_totals.count', 'float', true);
        $this->addField('quantity_ordered', 'orders_products_totals.quantity_ordered', 'float', true);
        $this->addField('quantity_split', 'orders_products_totals.quantity_split', 'float', true);
        $this->addField('total_price', 'orders_products_totals.total_price', 'float', true);
        $this->addField('quantity_picked', 'orders_products_totals.quantity_picked', 'float', true);
        $this->addField('quantity_skipped_picking', 'orders_products_totals.quantity_skipped_picking', 'float', true);
        $this->addField('quantity_not_picked', 'orders_products_totals.quantity_not_picked', 'float', true);
        $this->addField('quantity_shipped', 'orders_products_totals.quantity_shipped', 'float', true);
        $this->addField('quantity_to_pick', 'orders_products_totals.quantity_to_pick', 'float', true);
        $this->addField('quantity_to_ship', 'orders_products_totals.quantity_to_ship', 'float', true);
        $this->addField('max_updated_at', 'orders_products_totals.max_updated_at', 'datetime', true);

        $this->addField('shipping_method_code', 'orders.shipping_method_code', hidden: false);
        $this->addField('shipping_method_name', 'orders.shipping_method_name', hidden: false);
        $this->addField('picked_at', 'orders.picked_at', 'datetime', hidden: true);
        $this->addField('packed_at', 'orders.packed_at', 'datetime', hidden: true);

        $this->addField('order_fully_paid', DB::raw("CASE WHEN orders.is_fully_paid = 1 THEN 'Yes' ELSE 'No' END"), hidden: true);

        $this->addField('shipping_street_address_1', 'order_addresses_shipping.address1');
        $this->addField('shipping_street_address_2', 'order_addresses_shipping.address2');
        $this->addField('shipping_address_postcode', 'order_addresses_shipping.postcode');
        $this->addField('shipping_address_city', 'order_addresses_shipping.city');
        $this->addField('shipping_address_state_code', 'order_addresses_shipping.state_code');
        $this->addField('shipping_address_state', 'order_addresses_shipping.state_name');
        $this->addField('shipping_address_country_code', 'order_addresses_shipping.country_code');
        $this->addField('shipping_address_country', 'order_addresses_shipping.country_name');
        $this->addField('shipping_address_fax', 'order_addresses_shipping.fax');
        $this->addField('shipping_address_website', 'order_addresses_shipping.website');
        $this->addField('shipping_address_region', 'order_addresses_shipping.region');

        $this->addField('billing_street_address_1', 'order_addresses_billing.address1');
        $this->addField('billing_street_address_2', 'order_addresses_billing.address2');
        $this->addField('billing_address_postcode', 'order_addresses_billing.postcode');
        $this->addField('billing_address_city', 'order_addresses_billing.city');
        $this->addField('billing_address_state_code', 'order_addresses_billing.state_code');
        $this->addField('billing_address_state', 'order_addresses_billing.state_name');
        $this->addField('billing_address_country_code', 'order_addresses_billing.country_code');
        $this->addField('billing_address_country', 'order_addresses_billing.country_name');
        $this->addField('billing_address_fax', 'order_addresses_billing.fax');
        $this->addField('billing_address_website', 'order_addresses_billing.website');
        $this->addField('billing_address_region', 'order_addresses_billing.region');

        $this->addField('product_line_count', 'orders.product_line_count', 'number', hidden: false);

        $this->addField('order_active', DB::raw("CASE WHEN orders.is_active = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('order_on_hold', DB::raw("CASE WHEN orders.is_on_hold = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('order_editing', DB::raw("CASE WHEN orders.is_editing = 1 THEN 'Yes' ELSE 'No' END"));

        $this->addField('label_template', 'orders.label_template', hidden: false);
        $this->addField('deleted_at', 'orders.deleted_at', 'datetime');
        $this->addField('created_at', 'orders.created_at', 'datetime');
        $this->addField('updated_at', 'orders.updated_at', 'datetime');
    }
}
