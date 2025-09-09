<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\Intl\Languages;

/**
 * Class StoreTranslationRequest
 *
 * Handles validation for storing a translation resource.
 */
class StoreTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
            'target_language' => [
                'sometimes',
                'string',
                function (string $attribute, mixed $value, callable $fail): void {
                    if (!empty($value)) {
                        if (strlen((string) $value) !== 2 || !Languages::exists((string) $value)) {
                            $fail(
                                "The {$attribute} code '{$value}' is not valid. "
                                . "Please provide a valid ISO 639-1 code such as: en, es, fr."
                            );
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'The name is required.',
            'title.required'         => 'The title is required.',
            'description.required'   => 'The description is required.',
            'target_language.string' => 'The target language must be a valid ISO 639-1 string code, such as "en" or "es".',
        ];
    }
}
