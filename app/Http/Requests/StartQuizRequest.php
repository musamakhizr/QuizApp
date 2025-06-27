<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartQuizRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.regex' => 'The name must contain only letters and spaces.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name cannot exceed 100 characters.',
        ];
    }
}
