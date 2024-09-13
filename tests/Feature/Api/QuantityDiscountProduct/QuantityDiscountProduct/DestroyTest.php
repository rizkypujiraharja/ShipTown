<?php

namespace Tests\Feature\Api\QuantityDiscountProduct\QuantityDiscountProduct;

use App\Models\Product;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/quantity-discount-product/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'api'));

        /** @var QuantityDiscount $discount */
        $discount = QuantityDiscount::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var QuantityDiscountsProduct $productToDelete */
        $productToDelete = QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $discount->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->delete($this->uri.$productToDelete->id);

        ray($response->json());

        $response->assertOk();

        $this->assertSoftDeleted('modules_quantity_discounts_products', ['id' => $productToDelete->id]);
    }
}
