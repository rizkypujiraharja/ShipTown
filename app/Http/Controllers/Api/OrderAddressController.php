<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderAddressStoreRequest;
use App\Http\Requests\UpdateOrderAddressRequest;
use App\Http\Resources\OrderAddressResource;
use App\Models\OrderAddress;
use Illuminate\Http\Request;

class OrderAddressController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderAddress::getSpatieQueryBuilder()
            ->simplePaginate(request()->input('per_page', 10));

        return OrderAddressResource::collection($query);
    }

    public function store(OrderAddressStoreRequest $request)
    {
        $address = OrderAddress::create($request->validated());
        return OrderAddressResource::make($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderAddressRequest $request, OrderAddress $address): OrderAddressResource
    {
        $address->update($request->validated());

        return OrderAddressResource::make($address);
    }
}
