<?php

namespace App\Nova\Metrics\Trend;

use App\Earning;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class EarningsPerDay extends Trend
{
    use CacheKey;

    protected $resourceColumn = 'app_id';

    /**
     * Set resource column
     * @param $column
     *
     * @return $this
     */
    public function resourceColumn($column)
    {
        $this->resourceColumn = $column;

        return $this;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $model = $request->resourceId ? Earning::where($this->resourceColumn, $request->resourceId) : Earning::class;
        $trend = $this->sumByDays($request, $model, 'amount', 'charge_created_at')
                    ->dollars()
                    ->showLatestValue();

        if ($trend) {
            $trend->value /= 100;

            if ($trend->trend) {
                foreach ($trend->trend as $duration => $value) {
                    $trend->trend[$duration] = $value / 100;
                }
            }
        }

        return $trend;
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30    => '30 Days',
            60    => '60 Days',
            90    => '90 Days',
            180   => '180 Days',
            365   => '365 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'earnings-per-day';
    }
}
