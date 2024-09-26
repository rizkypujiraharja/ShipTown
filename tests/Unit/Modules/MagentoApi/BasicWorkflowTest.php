<?php

namespace Tests\Unit\Modules\MagentoApi;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Warehouse;
use App\Modules\MagentoApi\src\EventServiceProviderBase;
use App\Modules\MagentoApi\src\Jobs\CheckIfSyncIsRequiredJob;
use App\Modules\MagentoApi\src\Jobs\EnsureProductPriceIdIsFilledJob;
use App\Modules\MagentoApi\src\Jobs\EnsureProductRecordsExistJob;
use App\Modules\MagentoApi\src\Jobs\EnsureProductSkuIsFilledJob;
use App\Modules\MagentoApi\src\Jobs\FetchBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\FetchSpecialPricesJob;
use App\Modules\MagentoApi\src\Jobs\SyncProductBasePricesBulkJob;
use App\Modules\MagentoApi\src\Jobs\SyncProductSalePricesBulkJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Tests\TestCase;

class BasicWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EventServiceProviderBase::enableModule();
    }

    /** @test */
    public function test_module_basic_functionality(): void
    {
        if (empty(env('TEST_MODULES_MAGENTO2MSI_BASE_URL'))) {
            $this->markTestSkipped('Magento base url not set');
        }

        $warehouse = Warehouse::factory()->create();
        $product1 = Product::factory()->create(['sku' => '45'])->attachTag('Available Online');
        $product2 = Product::factory()->create(['sku' => '46'])->attachTag('Available Online');

        ProductPrice::query()->update([
            'price' => rand(1, 1000),
            'sale_price' => rand(1, 1000),
            'sale_price_start_date' => now()->subDays(rand(1, 10)),
            'sale_price_end_date' => now()->addDays(rand(1, 10)),
        ]);

        $magentoConnection = MagentoConnection::query()->create([
            'base_url' => env('TEST_MODULES_MAGENTO2MSI_BASE_URL'),
            'magento_store_id' => env('TEST_MODULES_MAGENTO2MSI_STORE_ID'),
            'pricing_source_warehouse_id' => $warehouse->id,
        ]);

        $magentoConnection->api_access_token = env('TEST_MODULES_MAGENTO2MSI_ACCESS_TOKEN');
        $magentoConnection->save();

        EnsureProductRecordsExistJob::dispatch();
        $this->assertDatabaseCount('modules_magento2api_products', 2);
        $this->assertDatabaseHas('modules_magento2api_products', ['product_id' => $product1->id]);
        $this->assertDatabaseHas('modules_magento2api_products', ['product_id' => $product2->id]);

        EnsureProductPriceIdIsFilledJob::dispatch();
        $this->assertDatabaseMissing('modules_magento2api_products', ['product_price_id' => null]);

        EnsureProductSkuIsFilledJob::dispatch();
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => null]);

        FetchBasePricesJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '45', 'exists_in_magento' => true]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'base_prices_fetched_at' => null]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'base_prices_raw_import' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'exists_in_magento' => false]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '46', 'base_prices_fetched_at' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'base_prices_raw_import' => null]);

        FetchSpecialPricesJob::dispatch();
        ray(MagentoProduct::query()->with('prices')->get()->toArray())->expand(2);

        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'special_prices_fetched_at' => null]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'special_prices_raw_import' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '45', 'exists_in_magento' => true]);

        CheckIfSyncIsRequiredJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['base_price_sync_required' => true]);
        $this->assertDatabaseHas('modules_magento2api_products', ['special_price_sync_required' => true]);

        SyncProductBasePricesBulkJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['base_prices_raw_import' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['base_price_sync_required' => null]);

        SyncProductSalePricesBulkJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['special_price_sync_required' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['special_prices_fetched_at' => null]);

        FetchBasePricesJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '45', 'exists_in_magento' => true]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'base_prices_fetched_at' => null]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'base_prices_raw_import' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'exists_in_magento' => false]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '46', 'base_prices_fetched_at' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'base_prices_raw_import' => null]);

        FetchSpecialPricesJob::dispatch();
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'special_prices_fetched_at' => null]);
        $this->assertDatabaseMissing('modules_magento2api_products', ['sku' => '45', 'special_prices_raw_import' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'exists_in_magento' => false]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'special_prices_fetched_at' => null]);
        $this->assertDatabaseHas('modules_magento2api_products', ['sku' => '46', 'special_prices_raw_import' => null]);

        CheckIfSyncIsRequiredJob::dispatch();
        $this->assertDatabaseHas('modules_magento2api_products', ['base_price_sync_required' => false]);
        $this->assertDatabaseHas('modules_magento2api_products', ['special_price_sync_required' => false]);
    }
}
