<?php

namespace Database\Seeders\Modules\Magento2MSI;

use App\Modules\Magento2MSI\src\Models\Magento2msiConnection;
use Illuminate\Database\Seeder;
use Spatie\Tags\Tag;

class ConnectionSeeder extends Seeder
{
    public function run(): void
    {
        if (empty(env('TEST_MODULES_MAGENTO2MSI_BASE_URL'))) {
            return;
        }

        $connection = Magento2msiConnection::query()->updateOrCreate([
            'base_url' => env('TEST_MODULES_MAGENTO2MSI_BASE_URL'),
            'magento_source_code' => env('TEST_MODULES_MAGENTO2MSI_MAGENTO_SOURCE_CODE', 'default'),
        ], [
            'api_access_token' => env('TEST_MODULES_MAGENTO2MSI_ACCESS_TOKEN'),
        ]);

        $tag = Tag::findOrCreate('ALL');
        $connection->inventory_source_warehouse_tag_id = $tag->id;
        $connection->save();

    }
}
