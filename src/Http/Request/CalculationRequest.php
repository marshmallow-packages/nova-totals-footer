<?php

namespace Marshmallow\NovaTotalsFooter\Http\Request;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\QueriesResources;

class CalculationRequest extends NovaRequest
{
    use QueriesResources;
}
