<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\CustomException;
use App\Foundations\FileHelper;
use App\Models\Post;
use App\Repositories\PostRepo;
use App\Services\PostService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    private PostService $postService;
    private $postRepoMock;
    private $fileHelperMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PostRepo
        $this->postRepoMock = Mockery::mock(PostRepo::class);

        // Mock FileHelper
        $this->fileHelperMock = Mockery::mock(FileHelper::class);

        // 建立 PostService 實例
        $this->postService = new PostService($this->postRepoMock, $this->fileHelperMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試建立貼文（無檔案）
     */
    public function test_create_post_without_files(): void
    {
        // 準備測試資料
        $data = [
            'member_id' => 1,
            'platform' => Post::PLATFORM_FACEBOOK,
            'page_id' => 123456789,
            'post_text' => '這是測試貼文',
            'post_at' => '2025-01-03 15:30:00',
        ];

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $expectedPost->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_SCHEDULED);
        $expectedPost->shouldReceive('getAttribute')->with('post_text')->andReturn('這是測試貼文');

        // Mock PostRepo 的 create 方法
        $this->postRepoMock
            ->shouldReceive('create')
            ->with(array_merge($data, ['status' => Post::STATUS_SCHEDULED]))
            ->once()
            ->andReturn($expectedPost);

        // 執行測試
        $result = $this->postService->createPost($data);

        // 驗證結果
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(Post::STATUS_SCHEDULED, $result->status);
        $this->assertEquals('這是測試貼文', $result->post_text);
    }

    /**
     * 測試建立貼文（有圖片）
     */
    public function test_create_post_with_image(): void
    {
        // 準備測試資料
        $data = [
            'member_id' => 1,
            'platform' => Post::PLATFORM_FACEBOOK,
            'page_id' => 123456789,
            'post_text' => '這是測試貼文',
            'post_at' => '2025-01-03 15:30:00',
        ];

        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        $imagePath = 'post_images/1/uuid.jpg';

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $expectedPost->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_SCHEDULED);
        $expectedPost->shouldReceive('setAttribute')->with('post_image', $imagePath)->andReturnSelf();
        $expectedPost->shouldReceive('save')->andReturn(true);

        // Mock PostRepo 的 create 方法
        $this->postRepoMock
            ->shouldReceive('create')
            ->with(array_merge($data, ['status' => Post::STATUS_SCHEDULED]))
            ->once()
            ->andReturn($expectedPost);

        // Mock FileHelper 的 uploadImage 方法
        $this->fileHelperMock
            ->shouldReceive('uploadImage')
            ->with($image, 1)
            ->once()
            ->andReturn($imagePath);

        // 執行測試
        $result = $this->postService->createPost($data, $image);

        // 驗證結果
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(Post::STATUS_SCHEDULED, $result->status);
    }

    /**
     * 測試建立貼文（有影片）
     */
    public function test_create_post_with_video(): void
    {
        // 準備測試資料
        $data = [
            'member_id' => 1,
            'platform' => Post::PLATFORM_FACEBOOK,
            'page_id' => 123456789,
            'post_text' => '這是測試貼文',
            'post_at' => '2025-01-03 15:30:00',
        ];

        $video = UploadedFile::fake()->create('test.mp4', 1000);
        $videoPath = 'post_videos/1/uuid.mp4';

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $expectedPost->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_SCHEDULED);
        $expectedPost->shouldReceive('setAttribute')->with('post_video', $videoPath)->andReturnSelf();
        $expectedPost->shouldReceive('save')->andReturn(true);

        // Mock PostRepo 的 create 方法
        $this->postRepoMock
            ->shouldReceive('create')
            ->with(array_merge($data, ['status' => Post::STATUS_SCHEDULED]))
            ->once()
            ->andReturn($expectedPost);

        // Mock FileHelper 的 uploadVideo 方法
        $this->fileHelperMock
            ->shouldReceive('uploadVideo')
            ->with($video, 1)
            ->once()
            ->andReturn($videoPath);

        // 執行測試
        $result = $this->postService->createPost($data, null, $video);

        // 驗證結果
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(Post::STATUS_SCHEDULED, $result->status);
    }

    /**
     * 測試建立貼文（有圖片和影片）
     */
    public function test_create_post_with_image_and_video(): void
    {
        // 準備測試資料
        $data = [
            'member_id' => 1,
            'platform' => Post::PLATFORM_FACEBOOK,
            'page_id' => 123456789,
            'post_text' => '這是測試貼文',
            'post_at' => '2025-01-03 15:30:00',
        ];

        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        $video = UploadedFile::fake()->create('test.mp4', 1000);
        $imagePath = 'post_images/1/uuid.jpg';
        $videoPath = 'post_videos/1/uuid.mp4';

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $expectedPost->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_SCHEDULED);
        $expectedPost->shouldReceive('setAttribute')->with('post_image', $imagePath)->andReturnSelf();
        $expectedPost->shouldReceive('setAttribute')->with('post_video', $videoPath)->andReturnSelf();
        $expectedPost->shouldReceive('save')->andReturn(true);

        // Mock PostRepo 的 create 方法
        $this->postRepoMock
            ->shouldReceive('create')
            ->with(array_merge($data, ['status' => Post::STATUS_SCHEDULED]))
            ->once()
            ->andReturn($expectedPost);

        // Mock FileHelper 的 uploadImage 方法
        $this->fileHelperMock
            ->shouldReceive('uploadImage')
            ->with($image, 1)
            ->once()
            ->andReturn($imagePath);

        // Mock FileHelper 的 uploadVideo 方法
        $this->fileHelperMock
            ->shouldReceive('uploadVideo')
            ->with($video, 1)
            ->once()
            ->andReturn($videoPath);

        // 執行測試
        $result = $this->postService->createPost($data, $image, $video);

        // 驗證結果
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(Post::STATUS_SCHEDULED, $result->status);
    }

    /**
     * 測試取得會員貼文列表
     */
    public function test_get_member_posts(): void
    {
        // 準備測試資料
        $memberId = 1;
        $page = 1;
        $limit = 10;
        $status = Post::STATUS_SCHEDULED;

        $expectedPaginator = new LengthAwarePaginator(
            collect([
                (object) ['id' => 1, 'member_id' => 1, 'post_text' => '貼文1'],
                (object) ['id' => 2, 'member_id' => 1, 'post_text' => '貼文2'],
            ]),
            2,
            10,
            1
        );

        // Mock PostRepo 的 getPostsByMemberId 方法
        $this->postRepoMock
            ->shouldReceive('getPostsByMemberId')
            ->with($memberId, $page, $limit, $status)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->postService->getMemberPosts($memberId, $page, $limit, $status);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertCount(2, $result->items());
    }

    /**
     * 測試取得單一貼文
     */
    public function test_get_post(): void
    {
        // 準備測試資料
        $memberId = 1;
        $postId = 1;

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn($postId);
        $expectedPost->shouldReceive('getAttribute')->with('member_id')->andReturn($memberId);
        $expectedPost->shouldReceive('getAttribute')->with('post_text')->andReturn('測試貼文');

        // Mock PostRepo 的 findByMemberIdAndId 方法
        $this->postRepoMock
            ->shouldReceive('findByMemberIdAndId')
            ->with($memberId, $postId)
            ->once()
            ->andReturn($expectedPost);

        // 執行測試
        $result = $this->postService->getPost($memberId, $postId);

        // 驗證結果
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals($postId, $result->id);
        $this->assertEquals($memberId, $result->member_id);
        $this->assertEquals('測試貼文', $result->post_text);
    }

    /**
     * 測試取得不存在的貼文
     */
    public function test_get_nonexistent_post(): void
    {
        // 準備測試資料
        $memberId = 1;
        $postId = 999;

        // Mock PostRepo 的 findByMemberIdAndId 方法
        $this->postRepoMock
            ->shouldReceive('findByMemberIdAndId')
            ->with($memberId, $postId)
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->postService->getPost($memberId, $postId);

        // 驗證結果
        $this->assertNull($result);
    }

    /**
     * 測試更新貼文（無檔案）
     */
    public function test_update_post_without_files(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('post_text')->andReturn('原始貼文');
        $post->shouldReceive('getAttribute')->with('post_image')->andReturn(null);
        $post->shouldReceive('getAttribute')->with('post_video')->andReturn(null);

        $data = [
            'post_text' => '更新後的貼文',
            'status' => Post::STATUS_PUBLISHED,
        ];

        // Mock PostRepo 的 update 方法
        $this->postRepoMock
            ->shouldReceive('update')
            ->with($post, $data)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->updatePost($post, $data);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試更新貼文（有圖片）
     */
    public function test_update_post_with_image(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('post_text')->andReturn('原始貼文');
        $post->shouldReceive('getAttribute')->with('post_image')->andReturn('post_images/1/old.jpg');
        $post->shouldReceive('getAttribute')->with('post_video')->andReturn(null);

        $image = UploadedFile::fake()->image('new.jpg', 100, 100);
        $newImagePath = 'post_images/1/new-uuid.jpg';

        $data = [
            'post_text' => '更新後的貼文',
        ];

        // Mock FileHelper 的 deleteFile 方法
        $this->fileHelperMock
            ->shouldReceive('deleteFile')
            ->with('post_images/1/old.jpg')
            ->once();

        // Mock FileHelper 的 uploadImage 方法
        $this->fileHelperMock
            ->shouldReceive('uploadImage')
            ->with($image, 1)
            ->once()
            ->andReturn($newImagePath);

        // Mock PostRepo 的 update 方法
        $this->postRepoMock
            ->shouldReceive('update')
            ->with($post, array_merge($data, ['post_image' => $newImagePath]))
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->updatePost($post, $data, $image);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試更新貼文（有影片）
     */
    public function test_update_post_with_video(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('post_text')->andReturn('原始貼文');
        $post->shouldReceive('getAttribute')->with('post_image')->andReturn(null);
        $post->shouldReceive('getAttribute')->with('post_video')->andReturn('post_videos/1/old.mp4');

        $video = UploadedFile::fake()->create('new.mp4', 1000);
        $newVideoPath = 'post_videos/1/new-uuid.mp4';

        $data = [
            'post_text' => '更新後的貼文',
        ];

        // Mock FileHelper 的 deleteFile 方法
        $this->fileHelperMock
            ->shouldReceive('deleteFile')
            ->with('post_videos/1/old.mp4')
            ->once();

        // Mock FileHelper 的 uploadVideo 方法
        $this->fileHelperMock
            ->shouldReceive('uploadVideo')
            ->with($video, 1)
            ->once()
            ->andReturn($newVideoPath);

        // Mock PostRepo 的 update 方法
        $this->postRepoMock
            ->shouldReceive('update')
            ->with($post, array_merge($data, ['post_video' => $newVideoPath]))
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->updatePost($post, $data, null, $video);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除貼文
     */
    public function test_delete_post(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('post_image')->andReturn('post_images/1/image.jpg');
        $post->shouldReceive('getAttribute')->with('post_video')->andReturn('post_videos/1/video.mp4');

        // Mock FileHelper 的 deleteFile 方法
        $this->fileHelperMock
            ->shouldReceive('deleteFile')
            ->with('post_images/1/image.jpg')
            ->once();

        $this->fileHelperMock
            ->shouldReceive('deleteFile')
            ->with('post_videos/1/video.mp4')
            ->once();

        // Mock PostRepo 的 delete 方法
        $this->postRepoMock
            ->shouldReceive('delete')
            ->with($post)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->deletePost($post);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除貼文（無檔案）
     */
    public function test_delete_post_without_files(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('post_image')->andReturn(null);
        $post->shouldReceive('getAttribute')->with('post_video')->andReturn(null);

        // Mock PostRepo 的 delete 方法
        $this->postRepoMock
            ->shouldReceive('delete')
            ->with($post)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->deletePost($post);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試取得檔案 URL
     */
    public function test_get_file_url(): void
    {
        // 準備測試資料
        $path = 'post_images/1/image.jpg';
        $expectedUrl = 'http://localhost/storage/post_images/1/image.jpg';

        // Mock FileHelper 的 getFileUrl 方法
        $this->fileHelperMock
            ->shouldReceive('getFileUrl')
            ->with($path)
            ->once()
            ->andReturn($expectedUrl);

        // 執行測試
        $result = $this->postService->getFileUrl($path);

        // 驗證結果
        $this->assertEquals($expectedUrl, $result);
    }

    /**
     * 測試驗證會員粉絲頁擁有權
     */
    public function test_validate_member_page_ownership(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = 123456789;

        // Mock PostRepo 的 checkMemberPageOwnership 方法
        $this->postRepoMock
            ->shouldReceive('checkMemberPageOwnership')
            ->with($memberId, $pageId)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->postService->validateMemberPageOwnership($memberId, $pageId);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試檢查貼文是否可以更新（排程中）
     */
    public function test_can_update_post_scheduled(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_SCHEDULED);

        // 執行測試
        $result = $this->postService->canUpdatePost($post);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試檢查貼文是否可以更新（已發佈）
     */
    public function test_can_update_post_published(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_PUBLISHED);

        // 執行測試
        $result = $this->postService->canUpdatePost($post);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試檢查貼文是否可以更新（已下架）
     */
    public function test_can_update_post_unpublished(): void
    {
        // 準備測試資料
        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getAttribute')->with('status')->andReturn(Post::STATUS_UNPUBLISHED);

        // 執行測試
        $result = $this->postService->canUpdatePost($post);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試檔案上傳失敗處理
     */
    public function test_create_post_file_upload_failure(): void
    {
        // 準備測試資料
        $data = [
            'member_id' => 1,
            'platform' => Post::PLATFORM_FACEBOOK,
            'page_id' => 123456789,
            'post_text' => '這是測試貼文',
            'post_at' => '2025-01-03 15:30:00',
        ];

        $image = UploadedFile::fake()->image('test.jpg', 100, 100);

        $expectedPost = Mockery::mock(Post::class);
        $expectedPost->shouldReceive('getAttribute')->with('id')->andReturn(1);

        // Mock PostRepo 的 create 方法
        $this->postRepoMock
            ->shouldReceive('create')
            ->with(array_merge($data, ['status' => Post::STATUS_SCHEDULED]))
            ->once()
            ->andReturn($expectedPost);

        // Mock FileHelper 的 uploadImage 方法拋出異常
        $this->fileHelperMock
            ->shouldReceive('uploadImage')
            ->with($image, 1)
            ->once()
            ->andThrow(new CustomException(CustomException::FILE_UPLOAD_FAILED));

        // 執行測試並驗證異常
        $this->expectException(CustomException::class);
        $this->expectExceptionMessage('檔案上傳失敗');

        $this->postService->createPost($data, $image);
    }
}
