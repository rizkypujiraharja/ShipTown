<?php

namespace App\Modules\DpdIreland\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Modules\DpdIreland\Dpd;
use App\Modules\DpdIreland\src\Exceptions\AuthorizationException;
use App\Modules\DpdIreland\src\Exceptions\ConsignmentValidationException;
use App\Modules\DpdIreland\src\Responses\PreAdvice;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NextDayShippingService extends ShippingServiceAbstract
{
    /**
     * @throws GuzzleException
     */
    public function ship(int $order_id): Collection
    {
        /** @var Order $order */
        $order = Order::findOrFail($order_id);

        $preAdvice = $this->createPreAdviceOrFail($order);

        $shippingLabel = new ShippingLabel;
        $shippingLabel->order_id = $order_id;
        $shippingLabel->user_id = Auth::guard('api')->id();
        $shippingLabel->carrier = 'DPD Ireland';
        $shippingLabel->service = 'next_day';
        $shippingLabel->shipping_number = $preAdvice->trackingNumber();
        $shippingLabel->tracking_url = 'https://dpd.ie/tracking?consignmentNumber='.$preAdvice->trackingNumber();
        $shippingLabel->content_type = ShippingLabel::CONTENT_TYPE_URL;
        $shippingLabel->base64_pdf_labels = base64_encode($preAdvice->labelImage());
        $shippingLabel->save();

        activity()
            ->on($order)
            ->by(auth()->user())
            ->log('generated shipping label '.$shippingLabel->shipping_number);

        return collect()->add($shippingLabel);
    }

    /**
     * @throws Exception|GuzzleException
     */
    private function createPreAdviceOrFail(Order $order): PreAdvice
    {
        try {
            $preAdvice = Dpd::shipOrder($order, request()->user());

            if ($preAdvice->isNotSuccess()) {
                throw new Exception('DPD Responded: '.$preAdvice->consignment()['RecordErrorDetails']);
            }

            return $preAdvice;
        } catch (ConsignmentValidationException $exception) {
            throw new ShippingServiceException('DPD: '.$exception->getMessage());
        } catch (AuthorizationException $exception) {
            throw new ShippingServiceException('DPD: Account authorization failed, contact administrator');
        }
    }
}
