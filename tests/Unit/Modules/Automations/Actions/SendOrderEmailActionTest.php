<?php

namespace Tests\Unit\Modules\Automations\Actions;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Modules\Automations\src\Actions\Order\SendOrderEmailAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOrderEmailActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_when_template_specified(): void
    {
        Mail::fake();

        /** @var MailTemplate $template */
        $template = MailTemplate::factory()->create([
            'code' => 'shipment_confirmation',
            'subject' => 'We shipped your order!',
            'mailable' => OrderMail::class,
            'html_template' => 'Hi Xyz, We shipped your order',
        ]);

        $order = Order::factory()->create();
        $action = new SendOrderEmailAction($order);

        // act
        $actionSucceeded = $action->handle($template->code);

        // validate
        $this->assertTrue($actionSucceeded, 'Action failed');
        Mail::assertSent(OrderMail::class);
    }
}
