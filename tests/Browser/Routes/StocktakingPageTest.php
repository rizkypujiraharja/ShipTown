<?php

namespace Tests\Browser\Routes;

use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Warehouse;
use App\User;
use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class StocktakingPageTest extends DuskTestCase
{
    private string $uri = '/stocktaking';

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
     * @throws Throwable
     */
    public function testIfPageDisplaysCorrectly(): void
    {
        $this->browse(function (Browser $browser) {
            /** @var User $user */
            $user = User::factory()->create([
                'warehouse_id' => Warehouse::factory()->create()->getKey(),
            ]);

            $browser->loginAs($user)
                ->visit($this->uri)
                ->pause($this->shortDelay)
                ->assertSee('PRODUCTS > STOCKTAKING')
                ->assertSee('SEE MORE')
                ->assertSee('REPORTS > STOCKTAKE SUGGESTIONS')
                ->assertSourceMissing('snotify-error')
                ->assertFocused('@barcode-input-field');
        });
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfulStocktakeAction(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            Warehouse::factory()->create();

            $browser->loginAs($user);
            $browser->visit($this->uri);
            $browser->pause($this->shortDelay);
            $browser->assertSourceMissing('snotify-error');

            Product::factory(3)->create()
                ->each(function (Product $product) use ($browser) {
                    $browser->assertFocused('@barcode-input-field');

                    $this->sendKeysTo($browser, $product->sku);
                    $this->sendKeysTo($browser, WebDriverKeys::ENTER);
                    $browser->pause($this->shortDelay);
                    $browser->pause($this->shortDelay);

                    $browser->assertFocused('@quantity-request-input');
                    $browser->assertSee($product->sku);
                    $browser->assertSee($product->name);

                    $this->sendKeysTo($browser, rand(0, 10000));
                    $this->sendKeysTo($browser, WebDriverKeys::ENTER);
                    $browser->pause($this->shortDelay);
                    $browser->pause($this->shortDelay);

                    $browser->assertSee('Stocktake updated');
                    $browser->assertMissing('#quantity-request-input');
                    $browser->assertFocused('@barcode-input-field');
                });
        });
    }

    /**
     * @throws Throwable
     */
    public function testIfNegativeQuantityNotAllowed(): void
    {
        $this->browse(function (Browser $browser) {
            Warehouse::factory()->create();

            /** @var User $user */
            $user = User::factory()->create();

            /** @var Product $product */
            $product = Product::factory()->create();

            $browser->loginAs($user)
                ->visit($this->uri)
                ->pause($this->shortDelay)
                ->assertSourceMissing('snotify-error');

            $this->sendKeysTo($browser, $product->sku);
            $this->sendKeysTo($browser, WebDriverKeys::ENTER);

            $browser->waitFor('#quantity-request-input')
                ->pause($this->shortDelay)
                ->assertSee($product->sku)
                ->assertSee($product->name);

            $this->sendKeysTo($browser, -1);
            $this->sendKeysTo($browser, WebDriverKeys::ENTER);

            $browser->waitForText('Minus quantity not allowed');
            $browser->assertVisible('#quantity-request-input');
            $browser->assertFocused('#quantity-request-input');

            $this->sendKeysTo($browser, WebDriverKeys::ESCAPE);

            $browser->pause($this->shortDelay);
            $browser->assertFocused('@barcode-input-field');
        });
    }

    /**
     * @throws Throwable
     */
    public function testIfAliasScans(): void
    {
        $this->browse(function (Browser $browser) {
            Warehouse::factory()->create();

            /** @var User $user */
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit($this->uri)
                ->pause($this->shortDelay)
                ->assertSourceMissing('snotify-error')
                ->assertFocused('@barcode-input-field');

            /** @var ProductAlias $alias */
            $alias = ProductAlias::query()->inRandomOrder()->first() ?? ProductAlias::factory()->create();

            $browser->assertFocused('@barcode-input-field');

            $this->sendKeysTo($browser, $alias->product->sku);
            $this->sendKeysTo($browser, WebDriverKeys::ENTER);

            $browser->waitFor('#quantity-request-input')
                ->pause($this->shortDelay)
                ->assertFocused('#quantity-request-input')
                ->assertSee($alias->product->sku)
                ->assertSee($alias->product->name);

            $this->sendKeysTo($browser, rand(0, 10000));
            $this->sendKeysTo($browser, WebDriverKeys::ENTER);

            $browser->waitForText('Stocktake updated')
                ->assertMissing('#quantity-request-input')
                ->pause($this->shortDelay)
                ->assertFocused('@barcode-input-field');
        });
    }

    /**
     * @throws Throwable
     */
    public function testIfNotifiesWhenProductNotFound(): void
    {
        $this->browse(function (Browser $browser) {
            Warehouse::factory()->create();
            $user = User::factory()->create();

            $browser
                ->loginAs($user)
                ->visit($this->uri)
                ->pause($this->shortDelay)
                ->assertSourceMissing('snotify-error')
                ->assertFocused('@barcode-input-field')
                ->type('@barcode-input-field', 'not-existing-sku')
                ->keys('@barcode-input-field', '{enter}')
                ->waitForText('Product not found')
                ->assertSourceHas('snotify-error');
        });
    }
}
