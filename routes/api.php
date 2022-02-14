<?php declare(strict_types=1);

use Dive\NovaTranslationEditor\Http\Controllers\PublishTranslationsController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationController;
use Dive\NovaTranslationEditor\Http\Controllers\TranslationGroupController;

/** @var \Illuminate\Routing\Router $router */
$router->get('/', [TranslationController::class, 'index']);
$router->put('/', [TranslationController::class, 'update']);
$router->delete('{id}', [TranslationController::class, 'destroy']);
$router->get('groups', [TranslationGroupController::class, 'index']);
$router->post('publish', PublishTranslationsController::class);
