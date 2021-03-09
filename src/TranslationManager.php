<?php

namespace Dive\NovaTranslationEditor;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationManager
{
    /**
     * Fetch language files for given locale.
     */
    public static function getFiles($locale = null) {
        $locale ??= config('nova-translation-editor.default_locale');
        $excludeFiles = collect(config('nova-translation-editor.exclude', []));

        return collect(File::allFiles(resource_path('lang/'.$locale)))
            ->map(fn (\SplFileInfo $file) => ltrim($file->getRelativePath().'/'.$file->getFilenameWithoutExtension(), '/'))
            // Filter excluded files
            ->filter(fn ($file) => ! $excludeFiles->contains($file))
            ->flatten();
    }

    public static function getFileLanguageLines($locale, $withValues = true, $file = null): Collection
    {
        $files = isset($file) ? collect($file) : self::getFiles($locale);

        // Fetch all translations for a given locale
        return $files
            // Read contents of every fetched file
            ->mapWithKeys(fn ($file) => [$file => Lang::get($file, [], $locale)])
            // File must contain a non empty array with translations
            ->filter(fn ($translation) => is_array($translation) && ! empty($translation))
            // Convert to dot notation
            ->map(fn ($translation, $key) => collect(Arr::dot($translation)))
            // Map to models
            ->flatMap(function ($translations, $file) use ($withValues) {
                return $translations->mapWithKeys(function ($value, $key) use ($file, $withValues) {
                    return [$file.'.'.$key => new LanguageLine([
                        'group' => $file,
                        'key' => $key,
                        'text' => $withValues ? $value : '',
                        'placeholder' => $value,
                    ])];
                });
            })
            ->flatten();
    }

    public static function publish() {
        // Save all translations to files for each locale

        // Fetch default locale translations without values
        $defaultFileTranslations = self::getFileLanguageLines(config('nova-translation-editor.default_locale'), false);
        $defaultFileTranslationsKeys = $defaultFileTranslations->map(fn ($translation) => $translation->getIdentifier())->toArray();

        foreach (config('nova-translation-editor.supported_locales') as $locale) {
            // Merge with current locale translations & exclude the keys that don't exist in the default locales
            $fileTranslations = self::getFileLanguageLines($locale)
                ->concat($defaultFileTranslations)
                ->unique(fn ($translation) => $translation->getIdentifier())
                ->filter(fn ($translation) => in_array($translation->getIdentifier(), $defaultFileTranslationsKeys));

            // Merge with DB translations
            $translationGroups = LanguageLine::where('locale', $locale)
                ->get()
                ->filter(fn ($translation) => in_array($translation->getIdentifier(), $defaultFileTranslationsKeys))
                ->concat($fileTranslations)
                ->unique(fn ($translation) => $translation->getIdentifier())
                ->sortBy(fn ($translation) => $translation->getIdentifier())
                // Only filter empty values for non-default locale
                ->filter(fn ($translation) => ($locale == config('nova-translation-editor.default_locale')) ?: $translation->text)
                ->map(fn ($translation) => $translation->only(['group', 'key', 'text']))
                ->groupBy('group')
                ->map(fn ($group) => $group->mapWithKeys(fn ($translation) => [$translation['key'] => $translation['text']]));

            foreach ($translationGroups as $group => $translations) {
                $translations = self::unDotArray($translations);

                if (preg_match('/(.*)\/[^\/]+$/', $group, $matches)) {
                    $path = resource_path('lang/'.$locale.'/'.$matches[1]);

                    if (! File::isDirectory($path)) {
                        File::makeDirectory($path, 493, true);
                    }
                }

                File::put(resource_path('lang/'.$locale.'/'.$group.'.php'), '<?php return '.var_export($translations, true).';');
            }
        }

        // Mark everything as published
        DB::table((new LanguageLine())->getTable())->update(['published_at' => Carbon::now()]);
    }

    private static function unDotArray($dottedArray)
    {
        $array = [];
        foreach ($dottedArray as $key => $value) {
            Arr::set($array, $key, $value);
        }

        return $array;
    }
}
