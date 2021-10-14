<?php

namespace Dive\NovaTranslationEditor;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaTranslationEditor extends Tool
{
    public function boot()
    {
        Nova::script('nova-translation-editor', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-translation-editor', __DIR__.'/../dist/css/tool.css');
    }

    public function renderNavigation()
    {
        return view('nova-translation-editor::navigation');
    }
}
