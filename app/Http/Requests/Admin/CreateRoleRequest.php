<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:roles,code',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
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
            'name.required' => '角色名稱不能為空',
            'name.string' => '角色名稱必須為字串',
            'name.max' => '角色名稱不能超過255個字元',
            'code.required' => '角色代碼不能為空',
            'code.string' => '角色代碼必須為字串',
            'code.max' => '角色代碼不能超過100個字元',
            'code.unique' => '角色代碼已存在',
            'description.string' => '角色描述必須為字串',
            'description.max' => '角色描述不能超過1000個字元',
            'status.required' => '狀態不能為空',
            'status.integer' => '狀態必須為整數',
            'status.in' => '狀態值無效',
            'permissions.array' => '權限必須為陣列',
            'permissions.*.integer' => '權限ID必須為整數',
            'permissions.*.exists' => '權限不存在',
        ];
    }
}
