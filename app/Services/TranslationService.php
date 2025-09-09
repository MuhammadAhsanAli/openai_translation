<?php

namespace App\Services;

use App\Models\Translation;
use App\Services\Contracts\TranslationServiceInterface;
use App\Jobs\ProcessTranslationJob;

/**
 * Class TranslationService
 *
 * Service class to handle translation-related operations.
 */
class TranslationService implements TranslationServiceInterface
{
    /**
     * Create a translation record and dispatch a processing job.
     *
     * @param array<string, mixed> $payload
     *
     * @return Translation
     */
    public function createAndDispatch(array $payload): Translation
    {
        $translation = Translation::create([
            'name'            => $payload['name'],
            'title'           => $payload['title'],
            'description'     => $payload['description'],
            'target_language' => $payload['target_language'] ?? null,
            'status'          => Translation::STATUS_PENDING,
        ]);

        ProcessTranslationJob::dispatch($translation);

        return $translation;
    }
}
