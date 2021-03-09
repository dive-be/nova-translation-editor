<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\LanguageLine;
use Illuminate\Http\Request;

class TranslationDeleteController
{
    public function __invoke(Request $request, $id)
    {
        LanguageLine::destroy($id);
    }
}
