<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAdminRequest extends FormRequest
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
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'status' => 'required|integer|in:0,1',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => '用戶名為必填項',
            'username.unique' => '用戶名已存在',
            'password.required' => '密碼為必填項',
            'password.min' => '密碼至少需要6個字符',
            'name.required' => '姓名為必填項',
            'email.required' => '郵箱為必填項',
            'email.email' => '郵箱格式不正確',
            'email.unique' => '郵箱已存在',
            'status.required' => '狀態為必填項',
            'status.in' => '狀態值不正確',
            'roles.array' => '角色格式不正確',
            'roles.*.exists' => '角色不存在',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => -1,
            'message' => '驗證失敗',
            'data' => $validator->errors(),
        ], 422));
    }
}
