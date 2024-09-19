<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StocktakesStoreRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class StocktakesController extends Controller
{
    public function store(StocktakesStoreRequest $request): AnonymousResourceCollection
    {
        $inventory = Inventory::find($request->get('product_id'), $request->get('warehouse_id'));

        $inventoryMovement = InventoryService::stocktake($inventory, $request->get('new_quantity'), [
            'description' => 'stocktake',
            'user_id' => Auth::guard('api')->id(),
        ]);

        return InventoryMovementResource::collection([$inventoryMovement]);
    }
}
