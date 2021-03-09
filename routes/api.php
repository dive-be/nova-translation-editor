<?php

use Dive\NovaTranslationEditor\Http\Controllers\TranslationDeleteController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationGroupIndexController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationIndexController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationPublishController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/translations', TranslationIndexController::class);
Route::post('/translations', TranslationUpdateController::class);
Route::delete('/translations/{id}', TranslationDeleteController::class);
Route::get('/translations/groups', TranslationGroupIndexController::class);
Route::post('/translations/publish', TranslationPublishController::class);
