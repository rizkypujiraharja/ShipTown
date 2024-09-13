<?php

namespace Tests\Browser\Routes\Tools\DataCollector;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class QuantityDiscountsTest extends DuskTestCase
{
    private string $uri = '/';

    /**
     * @throws Throwable
     */
    public function testBuy1get1FreeQuantityDiscount(): void
    {
        $this->markTestSkipped('Please ensure following test works');

        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create([
            'sku' => '4001',
            'Description' => 'T-Shirt Blue L',
            'price' => 15,
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->disableFitOnFailure();
            $browser->loginAs($user);
            $browser->visit($this->uri)
                ->pause($this->shortDelay)->clickLink('Menu')
                ->pause($this->shortDelay)->clickLink('Settings')
                ->pause($this->shortDelay)->clickLink('Modules')
                ->pause($this->shortDelay)->clickLink('Quantity Discounts Configuration')
                ->pause($this->shortDelay)->clickLink('New')
                ->pause($this->shortDelay)->typeSlowly('name', 'Buy 2 get 3rd half price')
                ->pause($this->shortDelay)->select('type', 'Buy X, get Y for Z percent discount')
                ->pause($this->shortDelay)->clickLink('Create')
                ->pause($this->shortDelay)->clickLink('Edit Configuration')
                ->pause($this->shortDelay)->typeSlowly('Quantity Full Price', '2')
                ->pause($this->shortDelay)->typeSlowly('Quantity Discounted Price', '1')
                ->pause($this->shortDelay)->typeSlowly('Discount Percent', '50')
                ->pause($this->shortDelay)->clickLink('Save')
                ->pause($this->shortDelay)->typeSlowly('input', '4001')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->assertSee($product->description)
                ->assertSee('Price: 15.00')
                ->pause($this->shortDelay)->clickLink('Tools')
                ->pause($this->shortDelay)->clickLink('Point Of Sale')
                ->pause($this->shortDelay)->typeSlowly('input', '4001')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->assertSee('Total: 25.00')
                ->pause($this->shortDelay)->typeSlowly('input', '4001')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->pause($this->shortDelay)->keys('input', 'enter')
                ->assertSee('Total: 50.00')
                ->assertSourceMissing('Server Error');
        });
    }
}
