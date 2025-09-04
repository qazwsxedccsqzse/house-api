<?php

declare(strict_types=1);

namespace App\Foundations;

use App\Exceptions\CustomException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 檔案處理輔助類別
 */
class FileHelper
{
    /**
     * 圖片支援的格式
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * 影片支援的格式
     */
    private const VIDEO_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv'];

    /**
     * 圖片最大大小 (10MB)
     */
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024;

    /**
     * 影片最大大小 (20MB)
     */
    private const MAX_VIDEO_SIZE = 20 * 1024 * 1024;

    /**
     * 上傳圖片
     */
    public function uploadImage(UploadedFile $image, int $postId): string
    {
        // 驗證檔案大小
        if ($image->getSize() > self::MAX_IMAGE_SIZE) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }

        // 驗證檔案格式
        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, self::IMAGE_EXTENSIONS)) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }

        // 生成檔名和路徑
        $filename = Str::uuid() . '.' . $extension;
        $path = "post_images/{$postId}/{$filename}";

        try {
            // 儲存檔案
            Storage::disk('public')->put($path, $image->getContent());
            return $path;
        } catch (\Exception $e) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }
    }

    /**
     * 上傳影片
     */
    public function uploadVideo(UploadedFile $video, int $postId): string
    {
        // 驗證檔案大小
        if ($video->getSize() > self::MAX_VIDEO_SIZE) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }

        // 驗證檔案格式
        $extension = strtolower($video->getClientOriginalExtension());
        if (!in_array($extension, self::VIDEO_EXTENSIONS)) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }

        // 生成檔名和路徑
        $filename = Str::uuid() . '.' . $extension;
        $path = "post_videos/{$postId}/{$filename}";

        try {
            // 儲存檔案
            Storage::disk('public')->put($path, $video->getContent());
            return $path;
        } catch (\Exception $e) {
            throw new CustomException(CustomException::FILE_UPLOAD_FAILED);
        }
    }

    /**
     * 刪除檔案
     */
    public function deleteFile(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * 取得檔案完整 URL
     */
    public function getFileUrl(string $path): string
    {
        return asset('storage/' . $path);
    }

    /**
     * 檢查檔案是否存在
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * 取得檔案大小
     */
    public function getFileSize(string $path): int
    {
        if (!$this->fileExists($path)) {
            return 0;
        }

        return Storage::disk('public')->size($path);
    }
}
