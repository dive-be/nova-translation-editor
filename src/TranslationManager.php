<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationManager
{
    public static function getFiles(?string $locale = null): Collection
    {
        $locale ??= config('nova-translation-editor.default_locale');
        $excludeFiles = collect(config('nova-translation-editor.exclude', []));

        return collect(File::allFiles(lang_path($locale)))
            ->map(static fn ($file) => ltrim($file->getRelativePath() . '/' . $file->getFilenameWithoutExtension(), '/'))
            // Filter excluded files
            ->filter(static fn ($file) => ! $excludeFiles->contains($file))
            ->flatten();
    }

    public static function getFileLanguageLines(string $locale, bool $withValues = true, $file = null): Collection
    {
        $files = isset($file) ? collect($file) : self::getFiles($locale);

        // Fetch all translations for a given locale
        return $files
            // Read contents of every fetched file
            ->mapWithKeys(static fn ($file) => [$file => Lang::get($file, [], $locale)])
            // File must contain a non empty array with translations
            ->filter(static fn ($trans) => is_array($trans) && ! empty($trans))
            // Convert to dot notation
            ->map(static fn ($trans, $key) => collect(Arr::dot($trans)))
            // Map to models
            ->flatMap(static function ($translations, $file) use ($withValues) {
                return $translations->mapWithKeys(function ($value, $key) use ($file, $withValues) {
                    return [$file . '.' . $key => new LanguageLine([
                        'group' => $file,
                        'key' => $key,
                        'text' => $withValues ? $value : '',
                        'placeholder' => $value,
                    ])];
                });
            })
            ->flatten();
    }

    public static function publish()
    {
        // Fetch default locale translations without values
        $defaultFileTranslations = self::getFileLanguageLines(config('nova-translation-editor.default_locale'), false);
        $defaultFileTranslationsKeys = $defaultFileTranslations
            ->map(static fn ($trans) => $trans->unique_id)
            ->toArray();

        foreach (config('nova-translation-editor.supported_locales') as $locale) {
            // Merge with current locale translations & exclude the keys that don't exist in the default locales
            $fileTranslations = self::getFileLanguageLines($locale)
                ->concat($defaultFileTranslations)
                ->unique(static fn ($trans) => $trans->unique_id)
                ->filter(static fn ($trans) => in_array($trans->unique_id, $defaultFileTranslationsKeys));

            // Merge with DB translations
            $translationGroups = LanguageLine::where('locale', $locale)
                ->get()
                ->filter(static fn ($trans) => in_array($trans->unique_id, $defaultFileTranslationsKeys))
                ->concat($fileTranslations)
                ->unique(static fn ($trans) => $trans->unique_id)
                ->sortBy(static fn ($trans) => $trans->unique_id)
                // Only filter empty values for non-default locale
                ->filter(static fn ($trans) => ($locale == config('nova-translation-editor.default_locale')) ?: $trans->text)
                ->map(static fn ($trans) => $trans->only(['group', 'key', 'text']))
                ->groupBy('group')
                ->map(static fn ($group) => $group->mapWithKeys(fn ($trans) => [$trans['key'] => $trans['text']]));

            foreach ($translationGroups as $group => $translations) {
                $translations = self::unDotArray($translations);

                if (preg_match('/(.*)\/[^\/]+$/', $group, $matches)) {
                    $path = lang_path($locale . '/' . $matches[1]);

                    if (! File::isDirectory($path)) {
                        File::makeDirectory($path, 493, true);
                    }
                }

                File::put(lang_path($locale . '/' . $group . '.php'), '<?php return ' . var_export($translations, true) . ';');
            }
        }

        // Mark everything as published
        DB::table((new LanguageLine())->getTable())->update(['published_at' => Carbon::now()]);
    }

    private static function unDotArray(Collection $dottedArray): array
    {
        $array = [];

        foreach ($dottedArray as $key => $value) {
            Arr::set($array, $key, $value);
        }

        return $array;
    }
}
