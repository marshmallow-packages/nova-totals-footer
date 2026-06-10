<?php

namespace Marshmallow\NovaTotalsFooter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Nova\Tool;
use Marshmallow\NovaTotalsFooter\NovaTotalsFooter;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Determine whether this tool belongs to the package.
     */
    public function matchesTool(Tool $tool): bool
    {
        return $tool instanceof NovaTotalsFooter;
    }
}
