<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mood_level' => 'required|integer|min:1|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'note' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'mood_level.required' => 'O nível de humor é obrigatório.',
            'mood_level.integer' => 'O nível de humor deve ser um número.',
            'mood_level.min' => 'O nível de humor deve estar entre 1 e 5.',
            'mood_level.max' => 'O nível de humor deve estar entre 1 e 5.',
            'tags.array' => 'As tags devem estar no formato correto.',
            'note.max' => 'A nota não pode ter mais de 2000 caracteres.',
        ];
    }
}
