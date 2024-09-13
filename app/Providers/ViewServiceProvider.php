<?php

namespace App\Providers;

use App\Models\NavigationMenu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.nav', function ($view) {
            $navigationMenu = Cache::remember('navigationMenu', now()->addMinutes(2), function () {
                return NavigationMenu::all();
            });

            $navigationMenuPicklist = $navigationMenu->where('group', 'picklist')->sortBy('name');
            $navigationMenuPacklist = $navigationMenu->where('group', 'packlist')->sortBy('name');
            $navigationMenuReports = $navigationMenu->where('group', 'reports')->sortBy('name');
            $view->with(compact('navigationMenuPicklist', 'navigationMenuPacklist', 'navigationMenuReports'));
        });
    }
}
