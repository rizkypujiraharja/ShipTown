<?php

namespace Database\Seeders;

use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Illuminate\Database\Seeder;

class RmsapiProductImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RmsapiProductImport::factory()->create();
    }
}
