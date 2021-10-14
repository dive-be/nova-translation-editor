<?php

namespace Dive\NovaTranslationEditor\Http\Middleware;

use Closure;
use Dive\NovaTranslationEditor\NovaTranslationEditor;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;

class Authorize
{
    public function handle(Request $request, Closure $next)
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return $tool?->authorize($request) ? $next($request) : abort(403);
    }

    public function matchesTool($tool): bool
    {
        return $tool instanceof NovaTranslationEditor;
    }
}
