<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleListRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => '頁碼必須為整數',
            'page.min' => '頁碼不能小於1',
            'limit.integer' => '每頁數量必須為整數',
            'limit.min' => '每頁數量不能小於1',
            'limit.max' => '每頁數量不能超過100',
            'search.string' => '搜尋關鍵字必須為字串',
            'search.max' => '搜尋關鍵字不能超過255個字元',
            'status.integer' => '狀態必須為整數',
            'status.in' => '狀態值無效',
        ];
    }
}
