<?php

namespace App\Modules\AddressLabel\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Services\OrderService;
use Illuminate\Support\Collection;

class AddressLabelShippingService extends ShippingServiceAbstract
{
    public function ship(int $order_id): Collection
    {
        /** @var Order $order */
        $order = Order::findOrFail($order_id);

        $pdfString = OrderService::getOrderPdf($order->order_number, 'address_label');

        // we create new shipping label and save it to database, so it can automatically be printed
        $shippingLabel = new ShippingLabel;
        $shippingLabel->order_id = $order->getKey();
        $shippingLabel->user_id = auth()->guard('api')->id();
        $shippingLabel->carrier = '';
        $shippingLabel->service = 'address_label';
        $shippingLabel->shipping_number = $order->order_number;
        $shippingLabel->content_type = ShippingLabel::CONTENT_TYPE_PDF;
        $shippingLabel->base64_pdf_labels = base64_encode($pdfString);
        $shippingLabel->save();

        activity()
            ->on($order)
            ->by(auth()->user())
            ->log('generated generic address label');

        // we delete label after it was printed
        $shippingLabel->delete();

        return collect([$shippingLabel]);
    }
}
