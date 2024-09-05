<?php

namespace App\Modules\Maintenance\src\Jobs\Products;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixQuantityAvailableJob extends UniqueJob
{
    public function handle(): void
    {
        $invalidProducts = Product::query()
            ->whereRaw(DB::getTablePrefix().'products.quantity - '.DB::getTablePrefix().'products.quantity_reserved != '.DB::getTablePrefix().'products.quantity_available')
            ->get()
            ->each(function (Product $product) {
                Log::warning('Incorrect quantity_available detected', [
                    'sku' => $product->sku,
                ]);

                // calling save method will recalculate
                $product->quantity_available = $product->quantity - $product->quantity_reserved;
                $product->save();
            });

        info('Finished FixQuantityAvailableJob', ['records_corrected' => $invalidProducts->count()]);
    }
}
