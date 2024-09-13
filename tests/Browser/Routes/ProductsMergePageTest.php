<?php

namespace Tests\Browser\Routes;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Exception;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class ProductsMergePageTest extends DuskTestCase
{
    private string $uri = '/products-merge';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (empty($this->uri)) {
            throw new Exception('Please set the $uri property in your test class.');
        }

        parent::setUp();

        Product::factory()->create(['sku' => 'sku1']);
        Product::factory()->create(['sku' => 'sku2']);
    }

    /**
     * @throws Throwable
     */
    public function testUserAccess(): void
    {
        $this->browse(function (Browser $browser) {
            /** @var User $user */
            $user = User::factory()->create();
            $user->assignRole('user');

            $user->warehouse()->associate(Warehouse::factory()->create());

            $browser->disableFitOnFailure()
                ->loginAs($user)
                ->visit($this->uri.'?sku1=sku1&sku2=sku2')
                ->pause(300)
                ->assertPathIs($this->uri)
                ->assertSourceMissing('snotify-error');
        });
    }

    /**
     * @throws Throwable
     */
    public function testAdminAccess(): void
    {
        $this->browse(function (Browser $browser) {
            /** @var User $admin */
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $browser->disableFitOnFailure();

            $browser->loginAs($admin);
            $browser->visit($this->uri.'?sku1=sku1&sku2=sku2');
            $browser->assertDontSee('SERVER ERROR')
                ->assertPathIs($this->uri)
                ->assertSourceMissing('snotify-error');
        });
    }

    /**
     * @throws Throwable
     */
    public function testGuestAccess(): void
    {
        User::factory()->create();

        $this->browse(function (Browser $browser) {
            $browser->disableFitOnFailure()
                ->logout()
                ->visit($this->uri.'?sku1=sku1&sku2=sku2')
                ->assertRouteIs('login')
                ->assertSee('Login');
        });
    }
}
