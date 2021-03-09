<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Http\Request;

class TranslationGroupIndexController
{
    public function __invoke(Request $request)
    {
        return response()->json(TranslationManager::getFiles());
    }
}
