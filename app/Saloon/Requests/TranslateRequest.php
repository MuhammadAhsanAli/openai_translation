<?php

namespace App\Saloon\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Class TranslateRequest
 *
 * Represents a request to OpenAI to translate a given text.
 */
class TranslateRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * HTTP method for the request.
     *
     * @var Method
     */
    protected Method $method = Method::POST;

    /**
     * TranslateRequest constructor.
     *
     * @param string      $text
     * @param string|null $targetLanguage
     */
    public function __construct(
        private readonly string $text,
        private readonly string|null $targetLanguage
    ) {
    }

    /**
     * Resolve the API endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/chat/completions';
    }

    /**
     * Define the default body of the request.
     *
     * @return array<string, mixed>
     */
    public function defaultBody(): array
    {
        $prompt = $this->buildPrompt();

        return [
            'model'     => config('translation.provider.model'),
            'messages'  => [
                [
                    'role'    => 'system',
                    'content' => 'You are a helpful translator. Translate the user-provided text and return only the translation in the response.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => config('translation.provider.temperature'),
            'max_tokens'  => config('translation.provider.max_tokens'),
        ];
    }

    /**
     * Build the translation prompt.
     *
     * @return string
     */
    private function buildPrompt(): string
    {
        return sprintf(
            "Translate the following text from %s to %s. Return only the translated text.\n\nText:\n%s",
            strtoupper(config('translation.source_language')),
            strtoupper((string) $this->targetLanguage),
            $this->text
        );
    }
}
