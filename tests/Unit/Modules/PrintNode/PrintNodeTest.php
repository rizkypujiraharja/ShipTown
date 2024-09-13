<?php

namespace Tests\Unit\Modules\PrintNode;

use App\Modules\PrintNode\src\Models\Client;
use App\Modules\PrintNode\src\PrintNode;
use App\User;
use Tests\TestCase;

class PrintNodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    public function test_if_api_key_set(): void
    {
        $apiKey = config('printnode.test_api_key');

        if (empty($apiKey)) {
            $this->markTestSkipped('TEST_MODULES_PRINTNODE_API_KEY env key is not set');
        }

        $this->assertNotEmpty($apiKey, 'TEST_MODULES_PRINTNODE_API_KEY env key is not set');
    }

    public function test_get_clients(): void
    {
        $apiKey = config('printnode.test_api_key');

        if (empty($apiKey)) {
            $this->markTestSkipped('TEST_MODULES_PRINTNODE_API_KEY env key is not set');
        }

        Client::query()->updateOrCreate([], ['api_key' => $apiKey]);

        $response = $this->get('api/modules/printnode/clients');

        $response->assertSuccessful();
    }

    public function test_get_printers(): void
    {
        $apiKey = config('printnode.test_api_key');

        if (empty($apiKey)) {
            $this->markTestSkipped('TEST_MODULES_PRINTNODE_API_KEY env key is not set');
        }

        Client::query()->updateOrCreate([], ['api_key' => $apiKey]);

        $response = $this->get('api/modules/printnode/printers');

        $response->assertSuccessful();
    }

    public function test_store_printjob(): void
    {
        $apiKey = config('printnode.test_api_key');

        if (empty($apiKey)) {
            $this->markTestSkipped('TEST_MODULES_PRINTNODE_API_KEY env key is not set');
        }

        Client::query()->updateOrCreate([], ['api_key' => $apiKey]);

        $printers = collect(PrintNode::getPrinters());

        $this->assertGreaterThan(0, $printers->count());

        $this->postJson('api/settings/modules/printnode/printjobs', [
            'printer_id' => $printers->first()['id'],
            'pdf_url' => 'https://api.printnode.com/static/test/pdf/label_6in_x_4in.pdf',
        ]);
    }
}
