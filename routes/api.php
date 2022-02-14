<?php declare(strict_types=1);

use Dive\NovaTranslationEditor\Http\Controllers\PublishTranslationsController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationGroupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TranslationController::class, 'index']);
Route::put('/', [TranslationController::class, 'update']);
Route::delete('{id}', [TranslationController::class, 'destroy']);
Route::get('groups', [TranslationGroupController::class, 'index']);
Route::post('publish', PublishTranslationsController::class);
