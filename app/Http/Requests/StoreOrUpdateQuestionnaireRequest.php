<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrUpdateQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        return [
            'sections' => 'required|array',
            'sections.*.id' => 'nullable|integer|exists:sections,id',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions' => 'required|array',
            'sections.*.questions.*.id' => 'nullable|integer|exists:questions,id',
            'sections.*.questions.*.text' => 'required|string|max:1000',
            'sections.*.questions.*.type' => 'required',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.options.*.id' => 'nullable|integer|exists:question_options,id',
            'sections.*.questions.*.options.*.option_text' => 'required|string|max:500',
        ];
    }
     protected function failedValidation(Validator $validator)
    {
        // Throw a JSON response when validation fails
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
