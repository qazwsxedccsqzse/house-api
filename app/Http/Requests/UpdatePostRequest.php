<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 更新貼文請求驗證
 */
class UpdatePostRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'platform' => 'nullable|integer|in:1,2',
            'page_id' => 'nullable|integer',
            'post_text' => 'nullable|string|max:2000',
            'post_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB
            'post_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:20480', // 20MB
            'post_at' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'nullable|integer|in:1,2,3',
        ];
    }

    /**
     * 取得自定義錯誤訊息
     */
    public function messages(): array
    {
        return [
            'platform.integer' => '平台必須為整數',
            'platform.in' => '平台必須為 1 (Facebook) 或 2 (Thread)',
            'page_id.integer' => '粉絲頁 ID 必須為整數',
            'post_text.string' => '貼文內容必須為字串',
            'post_text.max' => '貼文內容不能超過 2000 字',
            'post_image.image' => '上傳的檔案必須為圖片',
            'post_image.mimes' => '圖片格式必須為 jpeg, png, jpg',
            'post_image.max' => '圖片大小不能超過 10MB',
            'post_video.file' => '上傳的檔案必須為影片',
            'post_video.mimes' => '影片格式必須為 mp4, avi, mov, wmv',
            'post_video.max' => '影片大小不能超過 20MB',
            'post_at.date_format' => '發送時間格式必須為 Y-m-d H:i:s (例如: 2025-01-03 15:30:00)',
            'status.integer' => '狀態必須為整數',
            'status.in' => '狀態必須為 1 (排程中), 2 (已發佈) 或 3 (已下架)',
        ];
    }
}
