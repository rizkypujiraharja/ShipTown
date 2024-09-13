<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Events\EveryDayEvent;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Jobs\ResyncLastDayJob;
use App\Modules\Api2cart\src\Jobs\ResyncSyncErrorsTaggedJob;
use Illuminate\Support\Facades\DB;

class DailyEventListener
{
    /**
     * Handle the event.
     */
    public function handle(EveryDayEvent $event): void
    {
        ResyncLastDayJob::dispatch();
        ResyncSyncErrorsTaggedJob::dispatch();

        $this->recreateQuantityComparisonView();
        $this->recreatePricingComparisonView();
        $this->recreateComparisonView();
    }

    public function recreateQuantityComparisonView()
    {
        $warehouseIds = Warehouse::withAllTags(['magento_stock'])->get()->pluck('id')->collect();

        if ($warehouseIds->isEmpty()) {
            // so empty result will be returned
            $warehouseIds->push(0);
        }

        $query = '
CREATE OR REPLACE VIEW modules_api2cart_product_quantity_comparison_view AS
SELECT
    modules_api2cart_product_links.id                     AS product_link_id,
    modules_api2cart_product_links.is_in_sync             AS product_link_is_in_sync,
    FLOOR(MAX(api2cart_quantity))                         AS quantity_api2cart,
    IF(FLOOR(SUM(quantity_available)) < 0, 0, FLOOR(SUM(quantity_available))) AS quantity_expected

FROM modules_api2cart_product_links

LEFT JOIN modules_api2cart_connections
  ON modules_api2cart_connections.id = modules_api2cart_product_links.api2cart_connection_id

LEFT JOIN taggables
  ON taggables.tag_id = modules_api2cart_connections.inventory_source_warehouse_tag_id
  AND taggables.taggable_type = "App\\\\Models\\\\Warehouse"

LEFT JOIN warehouses
  ON warehouses.id = taggables.taggable_id

LEFT JOIN inventory
    ON inventory.product_id = modules_api2cart_product_links.product_id
    AND inventory.warehouse_id = warehouses.id

GROUP BY modules_api2cart_product_links.id
        ';

        DB::statement($query);
    }

    private function recreatePricingComparisonView()
    {
        $query = '
CREATE OR REPLACE VIEW modules_api2cart_product_pricing_comparison_view AS
SELECT
        modules_api2cart_product_links.id                                                   AS product_link_id,
        modules_api2cart_product_links.is_in_sync                                           AS product_link_is_in_sync,
       (api2cart_price = price)                                                             AS price_in_sync,
       (api2cart_sale_price = sale_price)                                                   AS sale_price_in_sync,
       (api2cart_sale_price_start_date = GREATEST("2000-01-01", sale_price_start_date))     AS sale_start_date_in_sync,
       (api2cart_sale_price_end_date = GREATEST("2000-01-01", sale_price_end_date))         AS sale_end_date_in_sync,


       api2cart_price                                       AS api2cart_price,
       price                                                AS actual_price,
       api2cart_sale_price                                  AS api2cart_sale_price,
       sale_price                                           AS actual_sale_price,
       api2cart_sale_price_start_date                       AS api2cart_sale_price_start_date,
       GREATEST("2000-01-01", sale_price_start_date)        AS actual_sale_price_start_date,
       api2cart_sale_price_end_date                         AS api2cart_sale_price_end_date,
       GREATEST("2000-01-01", sale_price_end_date)          AS actual_sale_price_end_date

FROM modules_api2cart_product_links

LEFT JOIN modules_api2cart_connections
  on modules_api2cart_connections.id = modules_api2cart_product_links.api2cart_connection_id

LEFT JOIN products_prices
  on products_prices.product_id = modules_api2cart_product_links.product_id
  and products_prices.warehouse_id = modules_api2cart_connections.pricing_source_warehouse_id
        ';

        DB::statement($query);
    }

    private function recreateComparisonView()
    {
        $query = '
CREATE OR REPLACE VIEW modules_api2cart_product_comparison_view AS
SELECT
  product_links.id,
  product_links.is_in_sync,
  product_links.product_id,
  products.sku,
  quantity_comparison_view.quantity_api2cart = quantity_comparison_view.quantity_expected as quantity_in_sync,
  pricing_comparison_view.price_in_sync,
  pricing_comparison_view.sale_price_in_sync,
  pricing_comparison_view.sale_start_date_in_sync,
  pricing_comparison_view.sale_end_date_in_sync,
  quantity_comparison_view.quantity_expected,
  quantity_comparison_view.quantity_api2cart,

  pricing_comparison_view.api2cart_price,
  pricing_comparison_view.actual_price,
  pricing_comparison_view.api2cart_sale_price,
  pricing_comparison_view.actual_sale_price,
  pricing_comparison_view.api2cart_sale_price_start_date,
  pricing_comparison_view.actual_sale_price_start_date,
  pricing_comparison_view.api2cart_sale_price_end_date,
  pricing_comparison_view.actual_sale_price_end_date

FROM modules_api2cart_product_links as product_links
LEFT JOIN modules_api2cart_product_pricing_comparison_view AS pricing_comparison_view
  ON pricing_comparison_view.product_link_id = product_links.id
LEFT JOIN modules_api2cart_product_quantity_comparison_view  AS quantity_comparison_view
  ON quantity_comparison_view.product_link_id = product_links.id
LEFT JOIN products
  ON products.id = product_links.product_id
        ';

        DB::statement($query);
    }
}
