<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class DashboardController extends Controller
{
    public function index(Request $request): Application|Factory|View|Redirector|RedirectResponse
    {
        $configuration = Configuration::first();

        if ($configuration->ecommerce_connected === false) {
            return redirect('quick-connect');
        }

        /** @var User $user */
        $user = $request->user();

        if (empty($user->default_dashboard_uri)) {
            return view('dashboard');
        }

        return redirect($user->default_dashboard_uri);
    }
}
