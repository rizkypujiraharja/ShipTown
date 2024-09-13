<?php

namespace App\Modules\AutoTags\src\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ToggleOversoldTagJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $product_id;

    public function __construct(int $product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var Product $product */
        $product = Product::find($this->product_id, ['id', 'quantity_available']);

        if ($product->quantity_available < 0) {
            $product->attachTag('oversold');
        } else {
            $product->detachTag('oversold');
        }
    }
}
