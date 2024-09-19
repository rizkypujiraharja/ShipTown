<?php

namespace App\Modules\BoxTop\src\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string SKUGroup
 * @property string SKUNumber
 * @property string SKUName
 * @property string Warehouse
 * @property array Attributes
 * @property string WarehouseQuantity
 * @property string AllocatedAllocated
 * @property string Available
 */
class WarehouseStock extends Model
{
    /**
     * @var string
     */
    protected $table = 'modules_boxtop_warehouse_stock';

    /**
     * @var string[]
     */
    protected $fillable = [
        'SKUGroup',
        'SKUNumber',
        'SKUName',
        'Warehouse',
        'Attributes',
        'WarehouseQuantity',
        'Allocated',
        'Available',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Attributes' => 'array',
        ];
    }
}
