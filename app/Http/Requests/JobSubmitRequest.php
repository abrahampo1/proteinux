<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class JobSubmitRequest extends FormRequest
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
            'fasta_sequence' => ['required', 'string', 'min:10', 'max:100000', 'regex:/^>/'],
            'fasta_filename' => ['required', 'string', 'max:255'],
            'gpus' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'cpus' => ['sometimes', 'integer', 'min:1', 'max:64'],
            'memory_gb' => ['sometimes', 'numeric', 'min:0.1', 'max:256'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fasta_sequence.regex' => 'The FASTA sequence must start with ">" (the header line).',
            'fasta_sequence.min' => 'The FASTA sequence is too short. It needs a header line and at least a few amino acids.',
        ];
    }
}
