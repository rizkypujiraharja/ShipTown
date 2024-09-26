<?php

namespace Tests\Feature\Api\Transactions\Transaction;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Warehouse;
use App\User;
use Spatie\Permission\Models\Role;
use Str;
use Tests\TestCase;

class PrintReceiptTest extends TestCase
{
    private string $uri = '/api/transaction/receipt-print';

    /** @test */
    public function test_printReceipt_route(): void
    {
        $warehouse = Warehouse::query()->inRandomOrder()->first() ?? Warehouse::factory()->create();

        /** @var User $user */
        $user = User::factory()->create([
            'warehouse_id' => $warehouse->getKey(),
            'warehouse_code' => $warehouse->code,
        ]);

        $user->assignRole(Role::findOrCreate('admin', 'api'));

        /** @var DataCollection $dataCollectionToUpdate */
        $dataCollectionToUpdate = DataCollection::factory()->create([
            'name' => 'Test Transaction',
            'type' => DataCollectionTransaction::class,
            'warehouse_id' => $user->warehouse_id,
            'warehouse_code' => $user->warehouse_code,
        ]);

        DataCollectionRecord::factory(rand(2, 5))
            ->create([
                'data_collection_id' => $dataCollectionToUpdate->id,
                'warehouse_id' => $user->warehouse_id,
                'warehouse_code' => $user->warehouse_code,
            ]);

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'id' => $dataCollectionToUpdate->id,
            'printer_id' => 1,
        ]);

        $receiptRawText = base64_decode($response->json('data.content'));

        $response->assertSuccessful();

        // todo fix and more assertions
        $this->assertTrue(Str::contains($receiptRawText, 'SKU         Name                     Qty.  Price'));
        $this->assertDatabaseHas(
            'data_collections',
            [
                'id' => $dataCollectionToUpdate->id,
                'name' => 'Test Transaction',
            ]
        );
    }
}
