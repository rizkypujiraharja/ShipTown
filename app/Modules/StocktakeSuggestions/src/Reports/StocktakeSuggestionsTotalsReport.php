<?php

namespace App\Modules\StocktakeSuggestions\src\Reports;

use App\Models\Warehouse;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class StocktakeSuggestionsTotalsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Stocktakes Suggestions';

        $this->baseQuery = Warehouse::query()
            ->select('warehouses.code as warehouse_code')
            ->selectRaw('(SELECT count(DISTINCT inventory_id)
                        FROM stocktake_suggestions
                        WHERE stocktake_suggestions.warehouse_id = warehouses.id) as count');

        $this->defaultSort = 'warehouse_code';

        $this->casts = [
            'count' => 'integer',
        ];

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
