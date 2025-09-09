<?php

namespace App\Saloon;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;

/**
 * Class OpenAiConnector
 *
 * Handles API connection to OpenAI for translation requests.
 */
class OpenAiConnector extends Connector
{
    /**
     * The base URL for the OpenAI API.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * OpenAiConnector constructor.
     */
    public function __construct()
    {
        $this->baseUrl = config('translation.provider.base_uri');
    }

    /**
     * Resolve the base URL for the connector.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Default headers to include with every request.
     *
     * @return array<string, string>
     */
    public function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('translation.provider.api_key'),
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Boot method runs for every request made with this connector.
     *
     * @param PendingRequest $pendingRequest
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->set([
            'timeout'         => 30,
            'connect_timeout' => 10,
        ]);
    }
}
