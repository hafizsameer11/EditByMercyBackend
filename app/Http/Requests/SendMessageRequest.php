<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendMessageRequest extends FormRequest
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
            'chat_id' => 'required|exists:chats,id',
            'type' => 'required|in:text,image,file,voice,order,video',
            'message' => 'nullable|string',
            'file' => 'nullable|file', // Max 10MB
            'duration' => 'nullable|integer',
            'order_id' => 'nullable|exists:orders,id',
            'is_forwarded' => 'nullable|boolean',
            'original_id' => 'nullable|exists:messages,id',
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
