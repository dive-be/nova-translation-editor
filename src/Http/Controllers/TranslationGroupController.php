<?php declare(strict_types=1);

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\TranslationManager;
use Illuminate\Http\JsonResponse;

class TranslationGroupController
{
    public function index(): JsonResponse
    {
        return response()->json(
            TranslationManager::getFiles()
        );
    }
}
