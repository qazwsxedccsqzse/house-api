<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\CustomException;

class DeleteMemberPageRequest extends FormRequest
{
    /**
     * 判斷使用者是否有權限進行此請求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 取得驗證規則
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page_ids' => 'required|array|min:1',
            'page_ids.*' => 'required|string|min:1',
        ];
    }

    /**
     * 取得驗證錯誤訊息
     */
    public function messages(): array
    {
        return [
            'page_ids.required' => 'page_ids 為必填欄位',
            'page_ids.array' => 'page_ids 必須是陣列格式',
            'page_ids.min' => 'page_ids 至少需要一個元素',
            'page_ids.*.required' => 'page_ids 中的每個元素都必須有值',
            'page_ids.*.string' => 'page_ids 中的每個元素都必須是字串',
            'page_ids.*.min' => 'page_ids 中的每個元素都不能為空字串',
        ];
    }

    /**
     * 驗證失敗時的處理
     */
    protected function failedValidation(Validator $validator): void
    {
        $errorMessage = $validator->errors()->first();
        throw new CustomException(CustomException::VALIDATION_FAILED, $errorMessage);
    }
}
