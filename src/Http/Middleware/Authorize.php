<?php

namespace Dive\NovaTranslationEditor\Http\Middleware;

use Dive\NovaTranslationEditor\NovaTranslationEditor;
use Laravel\Nova\Nova;

class Authorize
{
    public function handle($request, $next)
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return optional($tool)->authorize($request) ? $next($request) : abort(403);
    }

    public function matchesTool($tool)
    {
        return $tool instanceof NovaTranslationEditor;
    }
}
