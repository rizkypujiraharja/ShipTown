<?php

namespace Tests\Browser\Routes\Settings\MailTemplates\Mailtemplate;

use Tests\DuskTestCase;
use Throwable;

class PreviewPageTest extends DuskTestCase
{
    private string $uri = '/settings/mail-templates/{mailTemplate}/preview';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->markTestIncomplete();
    }
}
