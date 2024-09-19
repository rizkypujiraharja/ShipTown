<?php

namespace App\Modules\Api2cart\src\Models;

use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * App\Modules\Api2cart\src\Models\Api2cartOrderImports.
 *
 * @property int $id
 * @property int|null $connection_id
 * @property int|null $order_id
 * @property string|null $when_processed
 * @property string|null $order_number
 * @property int|null $api2cart_order_id
 * @property string|null $shipping_method_name
 * @property string|null $shipping_method_code
 * @property array $raw_import
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Api2cartConnection $api2cartConnection
 *
 * @method static Builder|Api2cartOrderImports newModelQuery()
 * @method static Builder|Api2cartOrderImports newQuery()
 * @method static Builder|Api2cartOrderImports query()
 * @method static Builder|Api2cartOrderImports whereConnectionId($value)
 * @method static Builder|Api2cartOrderImports whereCreatedAt($value)
 * @method static Builder|Api2cartOrderImports whereId($value)
 * @method static Builder|Api2cartOrderImports whereOrderId($value)
 * @method static Builder|Api2cartOrderImports whereOrderNumber($value)
 * @method static Builder|Api2cartOrderImports whereRawImport($value)
 * @method static Builder|Api2cartOrderImports whereUpdatedAt($value)
 * @method static Builder|Api2cartOrderImports whereWhenProcessed($value)
 *
 * @mixin Eloquent
 */
class Api2cartOrderImports extends BaseModel
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'modules_api2cart_order_imports';

    /**
     * @var string[]
     */
    protected $fillable = [
        'connection_id',
        'when_processed',
        'order_number',
        'raw_import',
    ];

    // we use attributes to set default values
    // we wont use database default values
    // as this is then not populated
    // correctly to events
    /**
     * @var string[]
     */
    protected $attributes = [
        'raw_import' => '{}',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_import' => 'array',
        ];
    }

    public function save(array $options = []): bool
    {
        $this->order_number = data_get($this->raw_import, 'id');
        $this->api2cart_order_id = data_get($this->raw_import, 'order_id');
        $this->shipping_method_name = data_get($this->raw_import, 'shipping_method.name', '');
        $this->shipping_method_code = data_get($this->raw_import, 'shipping_method.additional_fields.code');

        return parent::save($options);
    }

    public function api2cartConnection(): BelongsTo
    {
        return $this->belongsTo(Api2cartConnection::class, 'connection_id');
    }

    public function extractShippingAddressAttributes(): array
    {
        return array_filter([
            'company' => data_get($this->raw_import, 'shipping_address.company', ''),
            'gender' => data_get($this->raw_import, 'shipping_address.gender', ''),
            'first_name' => data_get($this->raw_import, 'shipping_address.first_name', ''),
            'last_name' => data_get($this->raw_import, 'shipping_address.last_name', ''),
            'email' => data_get($this->raw_import, 'customer.email', ''),
            'address1' => data_get($this->raw_import, 'shipping_address.address1', ''),
            'address2' => data_get($this->raw_import, 'shipping_address.address2', ''),
            'postcode' => data_get($this->raw_import, 'shipping_address.postcode', ''),
            'city' => data_get($this->raw_import, 'shipping_address.city', ''),
            'state_code' => data_get($this->raw_import, 'shipping_address.state.code', ''),
            'state_name' => data_get($this->raw_import, 'shipping_address.state.name', ''),
            'country_code' => data_get($this->raw_import, 'shipping_address.country.code3', ''),
            'country_name' => data_get($this->raw_import, 'shipping_address.country.name', ''),
            'phone' => data_get($this->raw_import, 'shipping_address.phone', ''),
            'fax' => data_get($this->raw_import, 'shipping_address.fax', ''),
            'website' => data_get($this->raw_import, 'shipping_address.website', ''),
            'region' => data_get($this->raw_import, 'shipping_address.region', ''),
        ]);
    }

    public function extractBillingAddressAttributes(): array
    {
        return array_filter([
            'company' => data_get($this->raw_import, 'billing_address.company', ''),
            'gender' => data_get($this->raw_import, 'billing_address.gender', ''),
            'first_name' => data_get($this->raw_import, 'billing_address.first_name', ''),
            'last_name' => data_get($this->raw_import, 'billing_address.last_name', ''),
            'email' => data_get($this->raw_import, 'customer.email', ''),
            'address1' => data_get($this->raw_import, 'billing_address.address1', ''),
            'address2' => data_get($this->raw_import, 'billing_address.address2', ''),
            'postcode' => data_get($this->raw_import, 'billing_address.postcode', ''),
            'city' => data_get($this->raw_import, 'billing_address.city', ''),
            'state_code' => data_get($this->raw_import, 'billing_address.state.code', ''),
            'state_name' => data_get($this->raw_import, 'billing_address.state.name', ''),
            'country_code' => data_get($this->raw_import, 'billing_address.country.code3', ''),
            'country_name' => data_get($this->raw_import, 'billing_address.country.name', ''),
            'phone' => data_get($this->raw_import, 'billing_address.phone', ''),
            'fax' => data_get($this->raw_import, 'billing_address.fax', ''),
            'website' => data_get($this->raw_import, 'billing_address.website', ''),
            'region' => data_get($this->raw_import, 'billing_address.region', ''),
        ]);
    }

    public function extractOrderProducts(): array
    {
        $result = [];

        foreach ($this->raw_import['order_products'] as $rawOrderProduct) {
            $result[] = [
                'sku_ordered' => $rawOrderProduct['model'],
                'name_ordered' => $rawOrderProduct['name'],
                'quantity_ordered' => $rawOrderProduct['quantity'],
                'price' => $rawOrderProduct['price'],
            ];
        }

        return $result;
    }

    public function extractStatusHistory(?array $order = null, bool $chronological = true): Collection
    {
        $statuses = Collection::make($this['raw_import']['status']['history']);

        if ($chronological) {
            $statuses = $statuses->sort(function ($a, $b) {
                $a_time = Carbon::make($a['modified_time']['value']);
                $b_time = Carbon::make($b['modified_time']['value']);

                return $a_time > $b_time;
            });
        }

        return $statuses;
    }

    public function ordersCreateAt(): Carbon
    {
        $create_at = $this->raw_import['create_at'];

        return Carbon::createFromFormat($create_at['format'], $create_at['value']);
    }
}
