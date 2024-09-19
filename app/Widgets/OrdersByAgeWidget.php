<?php

namespace App\Widgets;

use App\Models\Order;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersByAgeWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $orders_counts = Order::select([
            DB::raw('Date(order_placed_at) as date'),
            DB::raw('count(*) as order_count'),
        ])
            ->where(['is_active' => true])
            ->where(['is_on_hold' => false])
            ->groupBy(DB::raw('date(order_placed_at)'))
            ->orderBy('date', 'ASC')
            ->get();

        $total_count = $orders_counts->sum(function ($day) {
            return $day['order_count'];
        });

        return view('widgets.orders_by_age_widget', [
            'config' => $this->config,
            'orders_per_days_age' => $orders_counts->map(function ($var) {
                return [
                    'days_age' => (int) Carbon::make($var->date)->diffInDays(),
                    'order_count' => $var->order_count,
                ];
            }),
            'total_count' => $total_count,
        ]);
    }
}
