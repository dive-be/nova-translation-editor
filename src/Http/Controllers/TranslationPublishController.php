<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Http\Request;

class TranslationPublishController
{
    public function __invoke(Request $request)
    {
        TranslationManager::publish();
    }
}
