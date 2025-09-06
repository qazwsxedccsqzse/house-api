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
            // 重新載入貼文以獲取最新狀態
            $this->post->refresh();

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
            ]);

            // 取得粉絲頁的 access_token
            $memberPage = MemberPage::where('member_id', $this->post->member_id)
                ->where('page_id', $this->post->page_id)
                ->first();

            if (!$memberPage) {
                Log::channel('facebook')->error('找不到對應的粉絲頁', [
                    'post_id' => $this->post->id,
                    'member_id' => $this->post->member_id,
                    'page_id' => $this->post->page_id,
                ]);
                $this->markAsFailed();
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
                $this->markAsFailed();
                return;
            }

            // 更新貼文狀態為已發佈
            $postService->updatePostId($this->post, $fbPostId, Post::STATUS_PUBLISHED);

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
            $this->markAsFailed();
        }
    }

    /**
     * 標記任務為失敗
     */
    private function markAsFailed(): void
    {
        // 更新貼文狀態為發送失敗
        app(PostService::class)->updatePostId($this->post, '', Post::STATUS_SEND_FAILED);

        // 標記任務為失敗
        $this->fail();
    }

    /**
     * 任務失敗處理
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('facebook')->error('Facebook 貼文發送任務失敗', [
            'post_id' => $this->post->id,
            'member_id' => $this->post->member_id,
            'page_id' => $this->post->page_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
