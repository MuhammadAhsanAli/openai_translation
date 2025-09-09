<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\Contracts\TranslationServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Class TranslationController
 *
 * Handles translation resource operations for the API.
 */
class TranslationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TranslationServiceInterface $translationService
     */
    public function __construct(
        private readonly TranslationServiceInterface $translationService
    ) {
    }

    /**
     * Store a newly created translation and dispatch its processing job.
     *
     * @param StoreTranslationRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $translation = $this->translationService->createAndDispatch($payload);

        return response()->json([
            'success' => true,
            'data' => new TranslationResource($translation->fresh()),
        ], 201);
    }

    /**
     * Display the specified translation resource.
     *
     * @param Translation $translation
     * @return JsonResponse
     */
    public function show(Translation $translation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new TranslationResource($translation),
        ]);
    }
}
