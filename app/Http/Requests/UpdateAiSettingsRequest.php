<?php

namespace App\Http\Requests;

use App\Support\Ai\AiSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active_provider' => ['nullable', 'string', 'in:'.implode(',', AiSettings::PROVIDERS)],
            'providers' => ['sometimes', 'array'],
            'providers.anthropic.api_key' => ['nullable', 'string', 'max:500'],
            'providers.anthropic.model' => ['nullable', 'string', 'max:120'],
            'providers.openai.api_key' => ['nullable', 'string', 'max:500'],
            'providers.openai.model' => ['nullable', 'string', 'max:120'],
            'providers.gemini.api_key' => ['nullable', 'string', 'max:500'],
            'providers.gemini.model' => ['nullable', 'string', 'max:120'],
        ];
    }
}
