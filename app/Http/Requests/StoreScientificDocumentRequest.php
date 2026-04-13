<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScientificDocumentRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'in:paper,note,protocol,dataset'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,md,csv', 'max:51200'],
            'url' => ['nullable', 'url', 'max:500'],
            'doi' => ['nullable', 'string', 'max:255'],
            'protein_ids' => ['nullable', 'array'],
            'protein_ids.*' => ['exists:predicted_jobs,id'],
            'is_shared' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El titulo es obligatorio.',
            'type.in' => 'El tipo debe ser: paper, note, protocol o dataset.',
            'file.mimes' => 'El archivo debe ser PDF, DOC, DOCX, TXT, MD o CSV.',
            'file.max' => 'El archivo no puede superar los 50 MB.',
            'url.url' => 'La URL debe ser una direccion web valida.',
            'protein_ids.*.exists' => 'Una de las proteinas seleccionadas no existe.',
        ];
    }
}
