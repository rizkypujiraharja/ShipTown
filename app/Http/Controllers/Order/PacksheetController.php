<?php

namespace App\Http\Controllers\Order;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Http\Requests\PacksheetShowRequest;

class PacksheetController extends Controller
{
    public function show(PacksheetShowRequest $request, $order_id): View
    {
        return view('packsheet', ['order_id' => $order_id]);
    }
}
