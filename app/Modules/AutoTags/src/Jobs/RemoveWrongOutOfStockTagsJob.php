<?php

namespace App\Modules\AutoTags\src\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveWrongOutOfStockTagsJob implements ShouldQueue
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
        Log::debug('Starting RemoveWrongOutOfStockTagsJob');

        $invalidProducts = Product::withAllTags(['Out Of Stock'])
            ->where('quantity_available', '>', 0)
            ->get()
            ->each(function (Product $product) {
                $product->detachTag('Out Of Stock');
            });

        info('Finished RemoveWrongOutOfStockTagsJob', ['records_corrected' => $invalidProducts->count()]);
    }
}
