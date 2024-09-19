<?php

namespace App\Modules\InventoryMovements\src\Models;

use App\BaseModel;

/**
 * @property int $quantity_before_job_last_movement_id_checked
 * @property int $quantity_before_basic_job_last_movement_id_checked
 * @property int $quantity_before_stocktake_job_last_movement_id_checked
 */
class Configuration extends BaseModel
{
    protected $table = 'modules_inventory_movements_configurations';

    protected $fillable = [
        'quantity_before_job_last_movement_id_checked',
        'quantity_before_basic_job_last_movement_id_checked',
        'quantity_before_stocktake_job_last_movement_id_checked',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before_job_last_movement_id_checked' => 'integer',
            'quantity_before_basic_job_last_movement_id_checked' => 'integer',
            'quantity_before_stocktake_job_last_movement_id_checked' => 'integer',
        ];
    }
}
