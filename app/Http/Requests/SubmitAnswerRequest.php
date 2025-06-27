<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitAnswerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true; // Adjust authorization logic if needed
    }


    public function rules(): array
    {
        return [
            'question_id' => 'required|integer|exists:questions,id',
            'answer_id' => 'nullable|integer|exists:answers,id',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}
