<?php

namespace App\Http\Requests\Api\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Exceptions\CustomException;

class CommonPageRequest extends FormRequest
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
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'Page is required',
            'page.integer' => 'Page must be an integer',
            'page.min' => 'Page must be at least 1',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessage = $validator->errors()->first();
        throw new CustomException(CustomException::VALIDATION_FAILED, $errorMessage);
    }
}
