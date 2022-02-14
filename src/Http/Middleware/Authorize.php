<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor\Http\Middleware;

use Closure;
use Dive\NovaTranslationEditor\NovaTranslationEditor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Nova;

class Authorize
{
    public function handle(Request $request, Closure $next)
    {
        $tool = Arr::first(Nova::registeredTools(), $this->matchesTool(...));

        if (! $tool?->authorize($request)) {
            throw new AuthorizationException();
        }

        return $next($request);
    }

    private function matchesTool($tool): bool
    {
        return $tool instanceof NovaTranslationEditor;
    }
}
