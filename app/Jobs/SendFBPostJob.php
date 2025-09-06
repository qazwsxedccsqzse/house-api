<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Foundations\FileHelper;
use App\Foundations\Social\FB;
use App\Models\MemberPage;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * 發送 Facebook 貼文任務
 */
class SendFBPostJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大重試次數
     */
    public int $tries = 3;

    /**
     * 任務超時時間（秒）
     */
    public int $timeout = 60;

    /**
     * 重試間隔時間（秒）
     */
    public int $backoff = 60;

    public function __construct(
        private Post $post
    ) {}

    /**
     * 任務唯一識別符
     */
    public function uniqueId(): string
    {
        return "send_fb_post_{$this->post->id}";
    }

    /**
     * 執行任務
     */
    public function handle(PostService $postService, FB $fb, FileHelper $fileHelper): void
    {
        try {
            // 檢查貼文狀態，只處理發送中的貼文
            if ($this->post->status !== Post::STATUS_SENDING) {
                Log::channel('facebook')->info('貼文狀態已變更，跳過處理', [
                    'post_id' => $this->post->id,
                    'current_status' => $this->post->status,
                    'expected_status' => Post::STATUS_SENDING,
                ]);
                return;
            }

            Log::channel('facebook')->info('開始處理 Facebook 貼文發送', [
                'post_id' => $this->post->id,
                'member_id' => $this->post->member_id,
                'page_id' => $this->post->page_id,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
            ]);

            // 取得粉絲頁的 access_token
            $memberPage = $postService->getPostMemberPage($this->post);
            if (!$memberPage) {
                Log::channel('facebook')->error('找不到對應的粉絲頁', [
                    'post_id' => $this->post->id,
                    'member_id' => $this->post->member_id,
                    'page_id' => $this->post->page_id,
                ]);
                $this->handleError('找不到對應的粉絲頁', false);
                return;
            }

            $accessToken = $memberPage->access_token;
            $pageId = $this->post->page_id;
            $message = $this->post->post_text;

            // 處理圖片上傳和發送貼文
            $fbPostId = '';
            if (!empty($this->post->post_image)) {
                // 有圖片，先上傳圖片再發送貼文
                $imageUrl = $fileHelper->getFileUrl($this->post->post_image);
                $fbPostId = $fb->uploadImageAndPostToPage($pageId, $accessToken, $message, $imageUrl);
            } else {
                // 無圖片，直接發送貼文
                $fbPostId = $fb->postToPage($pageId, $accessToken, $message);
            }

            if (empty($fbPostId)) {
                Log::channel('facebook')->error('Facebook 貼文發送失敗', [
                    'post_id' => $this->post->id,
                    'member_id' => $this->post->member_id,
                    'page_id' => $this->post->page_id,
                ]);
                $this->handleError('Facebook 貼文發送失敗', true);
                return;
            }

            // 更新貼文狀態為已發佈
            $postService->updatePostId($this->post->id, ['post_id' => $fbPostId, 'status' => Post::STATUS_PUBLISHED]);

            Log::channel('facebook')->info('Facebook 貼文發送成功', [
                'post_id' => $this->post->id,
                'member_id' => $this->post->member_id,
                'page_id' => $this->post->page_id,
                'fb_post_id' => $fbPostId,
            ]);

        } catch (\Exception $e) {
            Log::channel('facebook')->error('Facebook 貼文發送異常', [
                'post_id' => $this->post->id,
                'member_id' => $this->post->member_id,
                'page_id' => $this->post->page_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->handleError($e->getMessage(), true);
        }
    }

    /**
     * 處理錯誤，決定是否重試
     */
    private function handleError(string $errorMessage, bool $shouldRetry = true): void
    {
        Log::channel('facebook')->warning('處理任務錯誤', [
            'post_id' => $this->post->id,
            'error' => $errorMessage,
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
            'should_retry' => $shouldRetry,
        ]);

        if (!$shouldRetry || $this->attempts() >= $this->tries) {
            // 達到重試上限或不可重試錯誤，標記為最終失敗
            $this->fail(new \Exception($errorMessage));
        } else {
            // 可重試錯誤，拋出異常讓 Laravel 自動重試
            throw new \Exception($errorMessage);
        }
    }

    /**
     * 任務失敗處理（所有重試都用盡後調用）
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('facebook')->error('Facebook 貼文發送任務最終失敗', [
            'post_id' => $this->post->id,
            'member_id' => $this->post->member_id,
            'page_id' => $this->post->page_id,
            'error' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);

        // 確保貼文狀態更新為失敗
        app(PostService::class)->updatePostId($this->post->id, ['status' => Post::STATUS_SEND_FAILED]);
    }
}
