<?php

namespace Marshmallow\NovaTotalsFooter\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Marshmallow\NovaTotalsFooter\NovaTotalsFooter;
use Marshmallow\NovaTotalsFooter\Http\Request\CalculationRequest;

class CalculateController extends Controller
{
    public function __invoke(CalculationRequest $request): JsonResponse
    {
        $result = [];

        if ($request->get('calculate') !== null) {
            foreach ($request->get('calculate') as $value) {
                $value = Str::isJson($value) ? json_decode($value, true) : $value;
                $index_name = Arr::get($value, 'indexName');
                $function = Arr::get($value, 'method');
                $decimals = Arr::get($value, 'decimals');
                if ($index_name && $function) {
                    $calculated_value = $request->toQuery()->{$function}($index_name);
                    Arr::set(
                        $result,
                        $index_name,
                        $this->formatCalculatedValue($calculated_value, $decimals)
                    );
                }
            }
        }

        return response()->json([
            'totals' => $result,
            'settings' => [
                'hideHeader' => NovaTotalsFooter::$hideHeader,
            ],
        ]);
    }

    protected function formatCalculatedValue(int|float $calculated_value, ?int $decimals = null): string
    {
        return number_format($calculated_value, $decimals ?? 0);
    }
}
