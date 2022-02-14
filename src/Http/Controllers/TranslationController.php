<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\LanguageLine;
use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TranslationController
{
    public function destroy(string $id)
    {
        LanguageLine::destroy($id);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['search' => ['nullable', 'string', 'max:255']]);

        $search = $request->get('search');
        $group = $request->get('group');

        $defaultLocale = config('nova-translation-editor.default_locale');
        $locale = $request->get('locale') ?? $defaultLocale;

        // Fetch default locale translations without values
        $defaultFileTranslations = TranslationManager::getFileLanguageLines($defaultLocale, false, $group);
        $defaultFileTranslationsKeys = $defaultFileTranslations
            ->map(fn ($translation) => $translation->unique_id)
            ->toArray();

        // Merge with current locale translations & exclude the keys that don't exist in the default locales
        $fileTranslations = TranslationManager::getFileLanguageLines($locale)
            ->concat($defaultFileTranslations)
            ->unique(fn ($translation) => $translation->unique_id)
            ->filter(fn ($translation) => in_array($translation->unique_id, $defaultFileTranslationsKeys));

        // Merge with DB translations
        $translations = LanguageLine::where('locale', $locale)
            ->get()
            ->filter(fn ($translation) => in_array($translation->unique_id, $defaultFileTranslationsKeys))
            ->concat($fileTranslations)
            ->unique(fn ($translation) => $translation->unique_id)
            ->sortBy(fn ($translation) => $translation->unique_id);

        if ($search) {
            $translations = $translations->filter(
                fn ($translation) => stripos($translation->text, $search) !== false || stripos($translation->key, $search) !== false
            );
        }

        return response()->json($translations->values());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
                'group' => 'string',
                'key' => 'string',
                'locale' => ['string', 'size:2'],
                'text' => 'string',
            ]) + ['published_at' => null];

        return response()->json(
            LanguageLine::updateOrCreate(Arr::only($validated, ['group', 'key', 'locale']), $validated)
        );
    }
}
