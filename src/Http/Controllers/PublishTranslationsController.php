<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\TranslationManager;

class PublishTranslationsController
{
    public function __invoke()
    {
        TranslationManager::publish();
    }
}
