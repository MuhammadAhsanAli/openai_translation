<?php

namespace App\Services\Contracts;

use App\Models\Translation;

/**
 * Interface TranslationServiceInterface
 *
 * Defines the contract for translation-related services.
 */
interface TranslationServiceInterface
{
    /**
     * Create a translation record and dispatch a processing job.
     *
     * @param array<string, mixed> $payload
     *
     * @return Translation
     */
    public function createAndDispatch(array $payload): Translation;
}
