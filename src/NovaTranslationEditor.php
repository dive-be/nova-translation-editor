<?php

namespace Dive\NovaTranslationEditor;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaTranslationEditor extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-translation-editor', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-translation-editor', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('nova-translation-editor::navigation');
    }
}
