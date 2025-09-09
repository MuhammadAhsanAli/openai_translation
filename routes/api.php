<?php

use App\Http\Controllers\Api\V1\TranslationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('translations', [TranslationController::class, 'store']);
    Route::get('translations/{translation}', [TranslationController::class, 'show']);
});
