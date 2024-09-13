<?php

namespace App\Modules\Maintenance\src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EnsureProductSkusPresentInAliasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::statement('
            DELETE FROM products_aliases
            WHERE ID IN (
                SELECT ID FROM (
                    SELECT products_aliases.id
                    FROM products
                    LEFT JOIN products_aliases ON products_aliases.alias = products.sku

                    WHERE products.id != products_aliases.product_id
                ) as tbl
            )
        ');

        DB::statement('
            INSERT INTO products_aliases (product_id, alias, created_at, updated_at)
            SELECT id, sku, now(), now()
            FROM `products`

            WHERE sku not in (select alias from products_aliases)
        ');
    }
}
