<?php

namespace Database\Seeders;

use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Illuminate\Database\Seeder;

class Api2CartOrderImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Api2cartOrderImports::factory()->create();
    }
}
