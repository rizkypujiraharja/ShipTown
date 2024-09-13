<?php

namespace Database\Seeders;

use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\RmsapiModuleServiceProvider;
use Illuminate\Database\Seeder;

class RmsapiConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(env('TEST_RMSAPI_WAREHOUSE_CODE'))) {
            return;
        }

        RmsapiModuleServiceProvider::enableModule();

        RmsapiConnection::factory()->create([
            'location_id' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
            'url' => env('TEST_RMSAPI_URL'),
            'username' => env('TEST_RMSAPI_USERNAME'),
            'password' => env('TEST_RMSAPI_PASSWORD'),
        ]);

        RmsapiModuleServiceProvider::enableModule();
    }
}
