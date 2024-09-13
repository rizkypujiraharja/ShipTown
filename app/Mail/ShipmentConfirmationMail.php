<?php

namespace App\Mail;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;

class ShipmentConfirmationMail extends TemplateMailable
{
    use Queueable, SerializesModels;

    public array $variables;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $template = MailTemplate::where('mailable', get_class($this))->first();

        if ($template->reply_to) {
            $this->replyTo($template->reply_to);
        }

        return $this;
    }
}
