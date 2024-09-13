<?php

namespace Tests\Browser\Routes;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class ProductsPageTest extends DuskTestCase
{
    private string $uri = '/products';

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        $this->basicUserAccessTest($this->uri, true);
        $this->basicAdminAccessTest($this->uri, true);
        $this->basicGuestAccessTest($this->uri);
    }

    /**
     * A basic browser test example.
     *
     *
     * @throws Throwable
     */
    public function testNoProducts(): void
    {
        Product::query()->forceDelete();

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->disableFitOnFailure()
                ->loginAs($user)
                ->visit($this->uri)
                ->pause(300)
                ->waitForText('No records found')
                ->type('@barcode-input-field', '')
                ->keys('@barcode-input-field', [WebDriverKeys::ENTER])
                ->pause(300)
                ->assertSourceMissing('snotify-error')
                ->assertFocused('@barcode-input-field');
        });
    }

    /**
     * @throws Throwable
     */
    public function testIfDisplaysProducts(): void
    {
        $this->browse(function (Browser $browser) {
            $warehouse = Warehouse::factory()->create();

            /** @var User $user */
            $user = User::factory()->create(['warehouse_id' => $warehouse->getKey()]);
            $user->assignRole('user');

            Product::factory()->create();

            $browser->disableFitOnFailure()
                ->loginAs($user)
                ->visit($this->uri);

            /** @var Product $product */
            $product = Product::factory()->create();

            $browser->keys('@barcode-input-field', $product->sku, '{enter}')->pause(101)
                ->waitForText($product->name)
                ->assertFocused('@barcode-input-field');
        });
    }
}
