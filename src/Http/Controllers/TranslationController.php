<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\LanguageLine;
use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Str;

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
            ->map(static fn ($translation) => $translation->unique_id)
            ->toArray();

        // Merge with current locale translations & exclude the keys that don't exist in the default locales
        $fileTranslations = TranslationManager::getFileLanguageLines($locale)
            ->concat($defaultFileTranslations)
            ->unique(static fn ($translation) => $translation->unique_id)
            ->filter(static fn ($translation) => in_array($translation->unique_id, $defaultFileTranslationsKeys));

        // Merge with DB translations
        $translations = LanguageLine::where('locale', $locale)
            ->get()
            ->filter(static fn ($translation) => in_array($translation->unique_id, $defaultFileTranslationsKeys))
            ->concat($fileTranslations)
            ->unique(static fn ($translation) => $translation->unique_id)
            ->sortBy(static fn ($translation) => $translation->unique_id);

        if ($search) {
            $translations = $translations->filter(static fn ($trans) =>
                Str::contains((string) $trans->text, $search, true) || Str::contains((string) $trans->key, $search, true)
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
