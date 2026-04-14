<?php

// Screen: result | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'result_date' => ['required', 'date'],
            'session'     => ['required', 'in:morning,noon,evening'],
            'results'     => ['required', 'array', 'min:1'],
            'results.*.position' => ['required', 'string', 'max:5'],
            'results.*.number'   => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'results.required' => 'At least one result entry is required.',
            'results.*.number.required' => 'Each position must have a number.',
        ];
    }
}
