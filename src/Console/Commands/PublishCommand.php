<?php

namespace Dive\NovaTranslationEditor\Console\Commands;

use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'nova-translation-editor:publish';

    protected $description = 'Publish all translations';

    public function handle()
    {
        TranslationManager::publish();
    }
}
