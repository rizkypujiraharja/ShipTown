<?php

namespace Tests\Unit\Modules\OversoldProductNotification;

use App\Mail\OversoldProductMail;
use App\Models\MailTemplate;
use App\Models\Product;
use App\Modules\OversoldProductNotification\src\OversoldProductNotificationServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        Mail::fake();

        OversoldProductNotificationServiceProvider::enableModule();

        MailTemplate::where(['mailable' => OversoldProductMail::class])
            ->update(['to' => 'arthur@youritsolutions.ie, arthur2@youritsolutions']);

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->attachTag('oversold');

        Mail::assertSent(OversoldProductMail::class, 1);
    }
}
