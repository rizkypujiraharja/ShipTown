<?php

namespace App\Modules\OversoldProductNotification\src\Jobs;

use App\Mail\OversoldProductMail;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $product_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mail = new OversoldProductMail([
            'product' => Product::find($this->product_id)->toArray(),
        ]);

        Mail::send($mail);
    }
}
