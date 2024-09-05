<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IsFullyPackedCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        if ($expected_value === '') {
            $expected_value = 'true';
        }

        $expectedBoolValue = filter_var($expected_value, FILTER_VALIDATE_BOOL);

        return $query->whereHas('orderProductsTotals', function ($query) use ($expectedBoolValue) {
            $query->whereRaw('(orders_products_totals.quantity_ordered > 0)')
                // ->where(DB::raw('(orders_products_totals.quantity_to_ship = 0)'), '=', $expectedBoolValue);
                ->whereRaw('(orders_products_totals.quantity_to_ship = 0) = ?', [$expectedBoolValue]);
        });
    }
}
