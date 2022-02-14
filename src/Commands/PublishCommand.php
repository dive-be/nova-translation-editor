<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor\Commands;

use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'nova-translation-editor:publish';

    protected $description = 'Publish all translations';

    public function handle(): int
    {
        TranslationManager::publish();

        $this->info('📝  Translation files published!');

        return self::SUCCESS;
    }
}
