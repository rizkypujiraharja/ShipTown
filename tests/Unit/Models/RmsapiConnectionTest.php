<?php

namespace Tests\Unit\Models;

use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Tests\TestCase;

class RmsapiConnectionTest extends TestCase
{
    public function testEncryptsPassword(): void
    {
        $config = new RmsapiConnection;
        $config->password = 'foo';

        // Tests that that password is encrypted before saving to the database.
        $this->assertNotEquals('foo', $config->password);
        // Make sure we can reuse the password when needed.
        $this->assertEquals('foo', \Crypt::decryptString($config->password));
    }
}
