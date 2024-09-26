<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PacklistOrderIndexRequest;
use App\Models\Order;
use App\Modules\Reports\src\Models\PacklistReport;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Class PacklistOrderController.
 */
class PacklistOrderController extends Controller
{
    public function index(PacklistOrderIndexRequest $request): JsonResource
    {
        // we clear packer ID from other orders first
        Order::query()
            ->where(['packer_user_id' => Auth::guard('api')->id()])
            ->whereNull('packed_at')
            ->get()
            ->each(function (Order $order) {
                $order->update(['packer_user_id' => null]);
            });

        $report = new PacklistReport;

        $orderList = $report->toJsonResource();

        if ($orderList['data']->isEmpty()) {
            $this->respondNotFound('No orders to pack');
        }

        /** @var Order $order */
        $order = $orderList['data'][0];

        $rowsUpdated = Order::query()
            ->where(['id' => $order->id])
            ->where(['updated_at' => $order->updated_at])
            ->whereNull('packer_user_id')
            ->update(['packer_user_id' => Auth::guard('api')->id()]);

        if ($rowsUpdated === 0) {
            $this->respondBadRequest('Order could not be reserved, try again');
        }

        // we update it once again trough Eloquent for events etc
        $order->update(['packer_user_id' => Auth::guard('api')->id()]);
        $order->log('received order for packing');

        return $orderList;
    }
}
