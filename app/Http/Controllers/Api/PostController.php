<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 貼文控制器
 */
class PostController extends BaseApiController
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * 建立貼文
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        $validatedData = $request->validate();
        $memberId = $request->member['id'];
        $data = [
            'member_id' => $memberId,
            'platform' => $validatedData['platform'],
            'page_id' => $validatedData['page_id'],
            'post_text' => $validatedData['post_text'],
            'post_at' => $validatedData['post_at'],
        ];

        $post = $this->postService->createPost(
            $data,
            $request->file('post_image'),
            $request->file('post_video')
        );

        // 處理檔案 URL
        if ($post->post_image) {
            $post->post_image_url = $this->postService->getFileUrl($post->post_image);
        }
        if ($post->post_video) {
            $post->post_video_url = $this->postService->getFileUrl($post->post_video);
        }

        return $this->success($post->load(['member', 'memberPage'])->toArray(), '貼文建立成功');
    }

    /**
     * 取得會員的貼文列表
     */
    public function index(Request $request): JsonResponse
    {
        $memberId = $request->member['id'];
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $status = $request->input('status') ? (int) $request->input('status') : null;

        // 驗證分頁參數
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $posts = $this->postService->getMemberPosts($memberId, $page, $limit, $status);

        // 處理檔案 URL
        $posts->through(function ($post) {
            if ($post->post_image) {
                $post->post_image_url = $this->postService->getFileUrl($post->post_image);
            }
            if ($post->post_video) {
                $post->post_video_url = $this->postService->getFileUrl($post->post_video);
            }
            return $post;
        });

        return $this->success([
            'posts' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    /**
     * 取得單一貼文
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $memberId = $request->member['id'];
        $post = $this->postService->getPost($memberId, $id);

        if (!$post) {
            return $this->error('貼文不存在', 404);
        }

        // 處理檔案 URL
        if ($post->post_image) {
            $post->post_image_url = $this->postService->getFileUrl($post->post_image);
        }
        if ($post->post_video) {
            $post->post_video_url = $this->postService->getFileUrl($post->post_video);
        }

        return $this->success($post->load(['member', 'memberPage'])->toArray(), '取得貼文成功');
    }

    /**
     * 更新貼文
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $memberId = $request->member['id'];
        $post = $this->postService->getPost($memberId, $id);

        if (!$post) {
            return $this->error('貼文不存在', 404);
        }

        // 檢查貼文是否可以更新
        if (!$this->postService->canUpdatePost($post)) {
            return $this->error('已發佈的貼文無法修改', 403);
        }

        $data = $request->only(['platform', 'page_id', 'post_text', 'post_at', 'status']);

        // 移除空值
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        $success = $this->postService->updatePost(
            $post,
            $data,
            $request->file('post_image'),
            $request->file('post_video')
        );

        if (!$success) {
            return $this->error('貼文更新失敗');
        }

        // 重新載入貼文資料
        $post->refresh();

        // 處理檔案 URL
        if ($post->post_image) {
            $post->post_image_url = $this->postService->getFileUrl($post->post_image);
        }
        if ($post->post_video) {
            $post->post_video_url = $this->postService->getFileUrl($post->post_video);
        }

        return $this->success($post->load(['member', 'memberPage'])->toArray(), '貼文更新成功');
    }

    /**
     * 刪除貼文
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $memberId = $request->member['id'];
        $post = $this->postService->getPost($memberId, $id);

        if (!$post) {
            return $this->error('貼文不存在', 404);
        }

        $success = $this->postService->deletePost($post);

        if (!$success) {
            return $this->error('貼文刪除失敗');
        }

        return $this->success([], '貼文刪除成功');
    }
}
