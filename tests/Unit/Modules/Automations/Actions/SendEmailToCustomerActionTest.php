<?php

namespace Tests\Unit\Modules\Automations\Actions;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Modules\Automations\src\Actions\Order\SendEmailToCustomerAction;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendEmailToCustomerActionTest extends TestCase
{
    public function test_successful_notification(): void
    {
        $order = Order::factory()->create();
        $orderShipment = ShippingLabel::factory()->create();

        $action = new SendEmailToCustomerAction($order);

        Mail::fake();

        // act
        $actionSucceeded = $action->handle('ready_for_collection_notification');

        Mail::assertSent(OrderMail::class, function ($mail) {
            $this->assertEquals('ready_for_collection_notification', $mail->getMailTemplate()->code);

            return true;
        });

        // validate
        $this->assertTrue($actionSucceeded, 'Action failed');
    }

    public function test_success_when_template_specified(): void
    {
        /** @var MailTemplate $template */
        $template = MailTemplate::factory()->create([
            'code' => 'shipment_confirmation',
            'subject' => 'test email',
            'mailable' => OrderMail::class,
            'html_template' => '',
        ]);

        $order = Order::factory()->create();

        $action = new SendEmailToCustomerAction($order);

        // act
        $actionSucceeded = $action->handle($template->code);

        // validate
        $this->assertTrue($actionSucceeded, 'Action failed');
    }
}
