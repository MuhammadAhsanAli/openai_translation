<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Jobs\ProcessTranslationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Class TranslationFlowTest
 *
 * Tests the full flow of creating translations and dispatching the processing job,
 * including validation and optional/edge cases.
 */
class TranslationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating a translation with valid payload dispatches a job.
     *
     * @return void
     */
    public function test_translation_create_dispatches_job(): void
    {
        Queue::fake();

        $payload = [
            'name'            => 'John Dan',
            'title'           => 'Test title',
            'description'     => 'This is test description.',
            'target_language' => 'es',
        ];

        $response = $this->postJson('/api/v1/translations', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('translations', [
            'name'   => 'John Dan',
            'title'  => 'Test title',
            'status' => Translation::STATUS_PENDING,
        ]);

        Queue::assertPushed(ProcessTranslationJob::class);
    }

    /**
     * Test creating a translation without target_language still works.
     *
     * @return void
     */
    public function test_translation_create_without_target_language(): void
    {
        Queue::fake();

        $payload = [
            'name'        => 'Jane Doe',
            'title'       => 'Hello',
            'description' => 'Sample description.',
        ];

        $response = $this->postJson('/api/v1/translations', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('translations', [
            'name'   => 'Jane Doe',
            'title'  => 'Hello',
            'status' => Translation::STATUS_PENDING,
        ]);

        Queue::assertPushed(ProcessTranslationJob::class);
    }

    /**
     * Test validation fails for invalid target_language codes.
     *
     * @return void
     */
    public function test_translation_create_invalid_target_language(): void
    {
        $payload = [
            'name'            => 'John Dan',
            'title'           => 'Test title',
            'description'     => 'This is test description.',
            'target_language' => 'xx',
        ];

        $response = $this->postJson('/api/v1/translations', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['target_language']);
    }

    /**
     * Test validation fails when required fields are missing.
     *
     * @return void
     */
    public function test_translation_create_missing_required_fields(): void
    {
        $payload = [];

        $response = $this->postJson('/api/v1/translations', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'title', 'description']);
    }
}
