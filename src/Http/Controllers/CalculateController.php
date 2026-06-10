<?php

namespace Marshmallow\NovaTotalsFooter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Marshmallow\NovaTotalsFooter\Http\Request\CalculationRequest;
use Marshmallow\NovaTotalsFooter\NovaTotalsFooter;

class CalculateController
{
    public function __invoke(CalculationRequest $request): JsonResponse
    {
        $totals = [];

        foreach ($request->get('calculate', []) as $calculation) {
            $calculation = Str::isJson($calculation) ? json_decode($calculation, true) : $calculation;

            $column = Arr::get($calculation, 'indexName');
            $method = Arr::get($calculation, 'method');
            $decimals = Arr::get($calculation, 'decimals');

            if (! $column || ! $method) {
                continue;
            }

            $value = $request->toQuery()->{$method}($column);

            Arr::set($totals, $column, $this->formatCalculatedValue($value, $decimals));
        }

        return response()->json([
            'totals' => $totals,
            'settings' => [
                'hideHeader' => NovaTotalsFooter::$hideHeader,
            ],
        ]);
    }

    protected function formatCalculatedValue(int|float $value, ?int $decimals = null): string
    {
        return number_format($value, $decimals ?? 0);
    }
}
