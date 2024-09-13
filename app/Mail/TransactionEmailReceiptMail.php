<?php

namespace App\Mail;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;

class TransactionEmailReceiptMail extends TemplateMailable
{
    use Queueable, SerializesModels;

    public array $variables;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MailTemplate $template, array $variables = [])
    {
        $this->mailTemplate = $template;
        $this->variables = $variables;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        if ($this->mailTemplate->reply_to) {
            $this->replyTo($this->mailTemplate->reply_to);
        }

        return $this;
    }
}
