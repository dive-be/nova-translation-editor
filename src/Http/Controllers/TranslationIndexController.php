<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\LanguageLine;
use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class TranslationIndexController
{
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'search' => 'string|nullable|max:255',
        ]);

        $search = $request->get('search');
        $group = $request->get('group');

        $defaultLocale = config('nova-translation-editor.default_locale');
        $locale = $request->get('locale') ?? $defaultLocale;

        // Fetch default locale translations without values
        $defaultFileTranslations = TranslationManager::getFileLanguageLines($defaultLocale, false, $group);
        $defaultFileTranslationsKeys = $defaultFileTranslations->map(fn ($translation) => $translation->getIdentifier())->toArray();

        // Merge with current locale translations & exclude the keys that don't exist in the default locales
        $fileTranslations = TranslationManager::getFileLanguageLines($locale)
            ->concat($defaultFileTranslations)
            ->unique(fn ($translation) => $translation->getIdentifier())
            ->filter(fn ($translation) => in_array($translation->getIdentifier(), $defaultFileTranslationsKeys));

        // Merge with DB translations
        $translations = LanguageLine::where('locale', $locale)
            ->get()
            ->filter(fn ($translation) => in_array($translation->getIdentifier(), $defaultFileTranslationsKeys))
            ->concat($fileTranslations)
            ->unique(fn ($translation) => $translation->getIdentifier())
            ->sortBy(fn ($translation) => $translation->getIdentifier());

        if ($search) {
            $translations = $translations->filter(fn ($translation) => stripos($translation->text, $search) !== false || stripos($translation->key, $search) !== false);
        }

        return response()->json($translations->values());
    }
}
