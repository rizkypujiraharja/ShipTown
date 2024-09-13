<?php

namespace Database\Seeders;

use App\Modules\PrintNode\src\Models\Client;
use App\Modules\PrintNode\src\PrintNodeServiceProvider;
use App\User;
use Illuminate\Database\Seeder;

class PrintNodeClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('TEST_MODULES_PRINTNODE_API_KEY')) {
            $client = Client::firstOrNew();
            $client->api_key = env('TEST_MODULES_PRINTNODE_API_KEY');
            $client->save();

            PrintNodeServiceProvider::enableModule();
        }

        if (env('TEST_MODULES_PRINTNODE_PRINTER_ID')) {
            User::query()->update(['printer_id' => env('TEST_MODULES_PRINTNODE_PRINTER_ID')]);
        }
    }
}
