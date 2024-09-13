<?php

namespace App\Mail;

use App\Models\MailTemplate;
use App\Traits\LogsActivityTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;

class OversoldProductMail extends TemplateMailable
{
    use LogsActivityTrait, Queueable, SerializesModels;

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
    public function build(): static
    {
        /** @var MailTemplate $template */
        $template = MailTemplate::query()->where(['mailable' => self::class])->first();

        $recipientsEmails = collect(explode(',', $template->to))
            ->map(function ($emailAddress) {
                return trim($emailAddress);
            });

        if ($recipientsEmails) {
            $this->to($recipientsEmails);
        }

        if ($template->reply_to) {
            $this->replyTo($template->reply_to);
        }

        return $this;
    }
}
