<?php

namespace App\Modules\Api2cart\src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class EnsureAllProductLinksExistJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tag = Tag::findFromString('Available Online');

        if ($tag === null) {
            return;
        }

        DB::statement('
        INSERT INTO modules_api2cart_product_links (api2cart_connection_id, product_id, updated_at, created_at)
        SELECT
          modules_api2cart_connections.id as api2cart_connection_id,
          taggable_id as product_id,
          now() as updated_at,
          now() as created_at

        FROM `taggables`

        LEFT JOIN modules_api2cart_connections
          ON 1=1

        LEFT JOIN modules_api2cart_product_links as product_link
          ON product_link.product_id = taggables.taggable_id
          AND product_link.api2cart_connection_id = modules_api2cart_connections.id

        WHERE tag_id = ? AND taggable_type = "App\\\\Models\\\\Product"

        AND product_link.id IS NULL
        ', [$tag->getKey()]);
    }
}
