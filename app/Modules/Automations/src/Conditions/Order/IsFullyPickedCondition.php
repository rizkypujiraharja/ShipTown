<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IsFullyPickedCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        if ($expected_value === '') {
            $expected_value = 'true';
        }

        $expected_value = filter_var($expected_value, FILTER_VALIDATE_BOOL);

        return $query->whereHas('orderProductsTotals', function ($query) use ($expected_value) {
            $query->whereRaw('(orders_products_totals.quantity_ordered > 0)')
                ->whereRaw('(orders_products_totals.quantity_to_pick = 0) = ?', [$expected_value]);
        });
    }
}
