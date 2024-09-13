<?php

namespace Tests\Feature\Api\Transaction\Receipt;

use App\Mail\TransactionEmailReceiptMail;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\OrderAddress;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private string $uri = '/api/transaction/receipt';
    private User $adminUser;
    private DataCollection $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(Role::findOrCreate('admin', 'api'));

        $shippingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
        $billingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $this->transaction = DataCollection::factory()->create([
            'name' => 'Test Transaction',
            'type' => \App\Models\DataCollectionTransaction::class,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $this->transaction->getKey(),
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'quantity_scanned' => rand(1, 10),
        ]);
    }

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $response = $this->actingAs($this->adminUser, 'api')->postJson($this->uri, ['id' => $this->transaction->id]);

        $response->assertSuccessful();

        Mail::assertSent(TransactionEmailReceiptMail::class, function ($mail) {
            return $mail->hasTo($this->transaction->shippingAddress->email);
        });
    }

    /** @test */
    public function testUserAccess(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, ['id' => $this->transaction->id]);

        $response->assertSuccessful();
    }
}
