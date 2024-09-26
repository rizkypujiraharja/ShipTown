<?php

namespace Tests;

use App\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Throwable;

abstract class DuskTestCase extends BaseTestCase
{
    use ResetsDatabase;

    protected int $superShortDelay = 50;

    protected int $shortDelay = 300;

    protected int $longDelay = 0;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--disable-search-engine-choice-screen',
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=300,650',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
            isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
            isset($_ENV['DUSK_START_MAXIMIZED']);
    }

    /**
     * @throws Throwable
     */
    public function basicUserAccessTest(string $uri, bool $allowed, ?User $user = null): void
    {
        $this->browse(function (Browser $browser) use ($uri, $allowed, $user) {
            /** @var User $visitor */
            $visitor = $user ?? User::factory()->create();
            $visitor->assignRole('user');

            $browser->disableFitOnFailure();

            $browser->loginAs($visitor);
            $browser->visit($uri);
            $browser->pause($this->shortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertSee('403');
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function basicAdminAccessTest(string $uri, bool $allowed): void
    {
        $this->browse(function (Browser $browser) use ($uri, $allowed) {
            /** @var User $admin */
            $admin = User::query()->inRandomOrder()->first() ?? User::factory()->create();
            $admin->assignRole('admin');

            $browser->disableFitOnFailure();

            $browser->loginAs($admin);
            $browser->visit($uri);
            $browser->pause($this->shortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertPathIs('login');
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function basicGuestAccessTest(string $uri, bool $allowed = false): void
    {
        User::factory()->create();

        $this->browse(function (Browser $browser) use ($uri, $allowed) {
            $browser->disableFitOnFailure();

            $browser->logout();
            $browser->visit($uri);
            $browser->pause($this->superShortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertPathIs('/login');
                $browser->assertSee('Login');
            }
        });
    }

    protected static function setEnvironmentValue($key, $value): void
    {
        $path = app()->environmentFilePath();

        if (! file_exists($path)) {
            return;
        }

        $str = file_get_contents($path);

        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$key}=");
        $endOfLinePosition = strpos($str, "\n", $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

        // If key does not exist, add it
        if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
            $str .= "{$key}={$value}\n";
        } else {
            $str = str_replace($oldLine, "{$key}={$value}", $str);
        }

        file_put_contents($path, $str);
    }

    protected function sendKeysTo(Browser $browser, string $keys): void
    {
        $browser->driver->getKeyboard()->sendKeys($keys);
    }
}
