<?php

namespace App\Jobs;

use App\Models\Translation;
use App\Saloon\OpenAiConnector;
use App\Saloon\Requests\TranslateRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Throwable;

/**
 * Class ProcessTranslationJob
 *
 * Handles translation of a Translation model via a queued job.
 */
class ProcessTranslationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Timeout in seconds for the job.
     *
     * @var int|null
     */
    public ?int $timeout = 120;

    /**
     * ProcessTranslationJob constructor.
     *
     * @param Translation $translation
     */
    public function __construct(
        private readonly Translation $translation
    ) {
    }

    /**
     * Handle the queued job.
     *
     * @throws Throwable
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function handle(): void
    {
        /** @var Translation|null $translation */
        $translation = Translation::where('id', $this->translation->id)
            ->where('status', '!=', Translation::STATUS_COMPLETED)
            ->first();

        if ($translation === null) {
            return;
        }

        // If no target language, mark as completed
        if (empty($translation->target_language)) {
            $translation->update(['status' => Translation::STATUS_COMPLETED]);
            return;
        }

        // Mark as processing
        $translation->update(['status' => Translation::STATUS_PROCESSING]);

        try {
            $connector = new OpenAiConnector();

            $request = new TranslateRequest(
                text: $translation->description,
                targetLanguage: $translation->target_language ?? null
            );

            $response = $connector->send($request);

            $payload = $response->json();

            if (empty($payload)) {
                throw new Exception('Empty response from translation provider.');
            }

            $dataToSave = [
                'provider_response' => $payload,
                'description'       => $this->extractTranslatedText($payload),
            ];

            $translation->update([
                'translated' => $dataToSave,
                'status'     => Translation::STATUS_COMPLETED,
            ]);
        } catch (Throwable $e) {
            Log::error('Translation failed', [
                'translation_id' => $this->translation->id,
                'error'          => $e->getMessage(),
            ]);

            $translation->update([
                'status' => Translation::STATUS_FAILED,
            ]);

            throw $e;
        }
    }

    /**
     * Extract a human-friendly translated text from provider response.
     * This method is defensive and can be extended for other providers.
     *
     * @param array<string, mixed> $payload
     *
     * @return string|null
     */
    private function extractTranslatedText(array $payload): ?string
    {
        return $payload['choices'][0]['message']['content'] ?? null;
    }
}
