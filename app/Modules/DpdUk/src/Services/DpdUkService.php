<?php

namespace App\Modules\DpdUk\src\Services;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Modules\DpdUk\src\Api\ApiClient;
use App\Modules\DpdUk\src\Models\Connection;
use App\User;
use Carbon\Carbon;
use Exception;

class DpdUkService
{
    private Connection $connection;

    private ApiClient $apiClient;

    public function __construct()
    {
        $this->connection = Connection::firstOrFail();

        $this->apiClient = new ApiClient($this->connection);
    }

    private function convertToDpdUkFormat(Order $order): array
    {
        try {
            /** @var User $user */
            $user = auth()->user();

            if ($user && isset($user->warehouse->address)) {
                $collectionAddress = $user->warehouse->address;
            } else {
                $collectionAddress = $this->connection->collectionAddress;
            }
        } catch (Exception $exception) {
            $collectionAddress = $this->connection->collectionAddress;
        }

        $shippingAddress = $order->shippingAddress;

        return [
            'jobId' => null,
            'collectionOnDelivery' => false,
            'invoice' => null,
            'collectionDate' => Carbon::today(),
            'consolidate' => false,
            'consignment' => [
                [
                    'consignmentNumber' => null,
                    'consignmentRef' => null,
                    'parcel' => [],
                    'collectionDetails' => [
                        'contactDetails' => [
                            'contactName' => $collectionAddress->full_name,
                            'telephone' => $collectionAddress->phone,
                        ],
                        'address' => [
                            'organisation' => $collectionAddress->company,
                            'countryCode' => self::replaceArray(['GBR' => 'GB'], $collectionAddress->country_code),
                            'postcode' => $collectionAddress->postcode,
                            'street' => $collectionAddress->address1,
                            'locality' => $collectionAddress->address2,
                            'town' => $collectionAddress->city,
                            'county' => $collectionAddress->state_code,
                        ],
                    ],
                    'deliveryDetails' => [
                        'contactDetails' => [
                            'contactName' => $shippingAddress->full_name,
                            'telephone' => $shippingAddress->phone,
                        ],
                        'address' => [
                            'organisation' => $shippingAddress->company,
                            'countryCode' => self::replaceArray(['GBR' => 'GB'], $shippingAddress->country_code),
                            'postcode' => $shippingAddress->postcode,
                            'street' => $shippingAddress->address1,
                            'locality' => $shippingAddress->address2,
                            'town' => $shippingAddress->city,
                            'county' => $shippingAddress->state_code,
                        ],
                        'notificationDetails' => [
                            'email' => $shippingAddress->email,
                            'mobile' => $shippingAddress->phone,
                        ],
                    ],
                    'networkCode' => '1^12',
                    'numberOfParcels' => 1,
                    'totalWeight' => 10,
                    'shippingRef1' => '#'.$order->order_number,
                    'shippingRef2' => '',
                    'shippingRef3' => '',
                    'customsValue' => null,
                    'deliveryInstructions' => '',
                    'parcelDescription' => '',
                    'liabilityValue' => null,
                    'liability' => false,
                ],
            ],
        ];
    }

    /**
     * @return array|string|string[]
     */
    public function replaceArray(array $replaceArray, string $subject)
    {
        return str_replace(array_keys($replaceArray), array_values($replaceArray), $subject);
    }

    private function generateTrackingUrl(ShippingLabel $orderShipment): string
    {
        $baseUlr = 'https://track.dpd.co.uk/search';
        $referenceParam = 'reference='.$orderShipment->shipping_number;
        $postcodeParam = 'postcode='.$orderShipment->order()->first()->shippingAddress->postcode;

        return $baseUlr.'?'.$referenceParam.'&'.$postcodeParam;
    }

    /**
     * @throws Exception
     */
    public function createShippingLabel(Order $order): ShippingLabel
    {
        $payload = $this->convertToDpdUkFormat($order);

        $dpdShipment = $this->apiClient->createShipment($payload);
        $dpdShippingLabel = $this->apiClient->getShipmentLabel($dpdShipment->getShipmentId());

        $content = $dpdShippingLabel->response->getBody()->getContents();

        $shipment = new ShippingLabel;
        $shipment->order_id = $order->id;
        $shipment->user_id = auth()->id();
        $shipment->carrier = 'DPD UK';
        $shipment->service = 'overnight';
        $shipment->shipping_number = $dpdShipment->getConsignmentNumber();
        $shipment->tracking_url = $this->generateTrackingUrl($shipment);
        $shipment->content_type = ShippingLabel::CONTENT_TYPE_RAW;
        $shipment->base64_pdf_labels = base64_encode($content);
        $shipment->save();

        return $shipment;
    }
}
