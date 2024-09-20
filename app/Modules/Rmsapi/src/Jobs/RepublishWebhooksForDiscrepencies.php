<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class RepublishWebhooksForDiscrepencies extends UniqueJob
{
    public function handle(): void
    {
        DB::statement('
            INSERT INTO modules_webhooks_pending_webhooks (model_class, model_id, created_at, updated_at)
            SELECT
                ? as model_class,
                modules_rmsapi_products_imports.inventory_id as model_id,
                now() as created_at,
                now() as updated_at
            FROM modules_rmsapi_products_imports
            LEFT JOIN inventory
              ON inventory.id = modules_rmsapi_products_imports.inventory_id
            WHERE inventory.quantity != modules_rmsapi_products_imports.quantity_on_hand
            AND inventory.updated_at < DATE_SUB(now(), INTERVAL 1 DAY)
            LIMIT 10000
        ', [Inventory::class]);
    }
}
