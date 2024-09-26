<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrdersController.
 */
class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::getSpatieQueryBuilder()
            ->simplePaginate(request()->input('per_page', 10))
            ->appends(request()->query());

        return OrderResource::collection($query);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::query()->updateOrCreate(
            [
                'order_number' => $request->validated()['order_number'],
                'order_placed_at' => $request->validated('order_placed_at', now()),
            ],
            $request->validated()
        );

        collect($request['products'])->each(function ($orderProductData) use ($order) {
            $product = Product::findBySKU($orderProductData['sku']);
            OrderProduct::create([
                'order_id' => $order->getKey(),
                'product_id' => $product ? $product->getKey() : null,
                'sku_ordered' => $orderProductData['sku'],
                'name_ordered' => $orderProductData['name'],
                'quantity_ordered' => $orderProductData['quantity'],
                'price' => $orderProductData['price'],
            ]);
        });

        return response()->json($order);
    }

    public function update(UpdateRequest $request, int $order_id): JsonResource
    {
        $order = Order::findOrFail($order_id);

        $attributes = $request->validated();

        if ($request->has('label_template') && $request->get('label_template') === null) {
            // we don't want turn off ConvertEmptyStringToNulls middleware
            $attributes['label_template'] = '';
        }

        if ($request->has('is_packed')) {
            if ($order->is_packed) {
                $this->respondNotAllowed405('Order already packed!');
            }

            if ($order->packed_at === null) {
                $attributes['packed_at'] = now();
            }

            $attributes['packer_user_id'] = $request->user()->getKey();
        }

        $order->update($attributes);

        return OrderResource::make($order->refresh());
    }

    public function show(Request $request, int $order_id): JsonResource
    {
        $order = Order::findOrFail($order_id);

        return new JsonResource($order);
    }
}
