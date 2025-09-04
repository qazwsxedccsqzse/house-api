<?php

declare(strict_types=1);

namespace App\Services;

use App\Foundations\FileHelper;
use App\Models\Post;
use App\Repositories\PostRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

/**
 * 貼文服務層
 */
class PostService
{
    public function __construct(
        private PostRepo $postRepo,
        private FileHelper $fileHelper
    ) {}

    /**
     * 建立貼文
     */
    public function createPost(array $data, ?UploadedFile $image = null, ?UploadedFile $video = null): Post
    {
        // 統一設定狀態為排程中
        $data['status'] = Post::STATUS_SCHEDULED;

        // 先建立貼文以取得 post_id
        $post = $this->postRepo->create($data);

        // 處理圖片上傳
        if ($image) {
            $post->post_image = $this->fileHelper->uploadImage($image, $post->id);
            $post->save();
        }

        // 處理影片上傳
        if ($video) {
            $post->post_video = $this->fileHelper->uploadVideo($video, $post->id);
            $post->save();
        }

        return $post;
    }

    /**
     * 取得會員的貼文列表
     */
    public function getMemberPosts(
        int $memberId,
        int $page = 1,
        int $limit = 10,
        ?int $status = null
    ): LengthAwarePaginator {
        return $this->postRepo->getPostsByMemberId($memberId, $page, $limit, $status);
    }

    /**
     * 取得單一貼文
     */
    public function getPost(int $memberId, int $postId): ?Post
    {
        return $this->postRepo->findByMemberIdAndId($memberId, $postId);
    }

    /**
     * 更新貼文
     */
    public function updatePost(
        Post $post,
        array $data,
        ?UploadedFile $image = null,
        ?UploadedFile $video = null
    ): bool {
        // 處理圖片上傳
        if ($image) {
            // 刪除舊圖片
            if ($post->post_image) {
                $this->fileHelper->deleteFile($post->post_image);
            }
            $data['post_image'] = $this->fileHelper->uploadImage($image, $post->id);
        }

        // 處理影片上傳
        if ($video) {
            // 刪除舊影片
            if ($post->post_video) {
                $this->fileHelper->deleteFile($post->post_video);
            }
            $data['post_video'] = $this->fileHelper->uploadVideo($video, $post->id);
        }

        return $this->postRepo->update($post, $data);
    }

    /**
     * 刪除貼文
     */
    public function deletePost(Post $post): bool
    {
        // 刪除相關檔案
        if ($post->post_image) {
            $this->fileHelper->deleteFile($post->post_image);
        }
        if ($post->post_video) {
            $this->fileHelper->deleteFile($post->post_video);
        }

        return $this->postRepo->delete($post);
    }

    /**
     * 取得檔案完整 URL
     */
    public function getFileUrl(string $path): string
    {
        return $this->fileHelper->getFileUrl($path);
    }

    /**
     * 驗證會員是否擁有該粉絲頁
     */
    public function validateMemberPageOwnership(int $memberId, int $pageId): bool
    {
        return $this->postRepo->checkMemberPageOwnership($memberId, $pageId);
    }

    /**
     * 檢查貼文是否可以更新
     */
    public function canUpdatePost(Post $post): bool
    {
        return $post->status !== Post::STATUS_PUBLISHED;
    }
}
