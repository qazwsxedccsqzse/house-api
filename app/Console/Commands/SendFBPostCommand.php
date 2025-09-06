<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendFBPostJob;
use App\Services\PostService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 發送 Facebook 貼文命令
 */
class SendFBPostCommand extends Command
{
    /**
     * 命令簽名
     */
    protected $signature = 'fb:send-post {--limit=3 : 每次處理的貼文數量}';

    /**
     * 命令描述
     */
    protected $description = '發送排程中的 Facebook 貼文';

    public function __construct(
        private PostService $postService
    ) {
        parent::__construct();
    }

    /**
     * 執行命令
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        Log::channel('facebook')->info('開始執行 Facebook 貼文發送命令', ['limit' => $limit]);

        try {
            // 取得排程中的貼文
            $posts = $this->postService->getScheduledPosts($limit);

            if ($posts->isEmpty()) {
                Log::channel('facebook')->info('沒有需要發送的排程貼文');
                $this->info('沒有需要發送的排程貼文');
                return self::SUCCESS;
            }

            $this->info("找到 {$posts->count()} 篇排程貼文，開始分發任務...");

            // 將每篇貼文分發給 Job
            foreach ($posts as $post) {
                SendFBPostJob::dispatch($post);
                $this->line("已分發貼文 ID: {$post->id} 到發送佇列");
            }

            Log::channel('facebook')->info('Facebook 貼文發送命令執行完成', [
                'processed_count' => $posts->count(),
                'post_ids' => $posts->pluck('id')->toArray(),
            ]);

            $this->info("已分發 {$posts->count()} 篇貼文到發送佇列");
            return self::SUCCESS;

        } catch (\Exception $e) {
            Log::channel('facebook')->error('Facebook 貼文發送命令執行失敗', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("命令執行失敗: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
