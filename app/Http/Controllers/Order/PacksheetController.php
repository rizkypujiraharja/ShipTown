<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\PacksheetShowRequest;
use Illuminate\View\View;

class PacksheetController extends Controller
{
    public function show(PacksheetShowRequest $request, $order_id): View
    {
        return view('packsheet', ['order_id' => $order_id]);
    }
}
