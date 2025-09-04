# Posts API 文檔

## 概述
此 API 提供貼文管理功能，包括建立、讀取、更新和刪除貼文。

## 認證
所有 API 都需要使用 `member.token` 中間件進行認證。

## API 端點

### 1. 建立貼文
**POST** `/api/v1/user/posts`

#### 請求參數
- `platform` (必填): 平台類型 (1: Facebook, 2: Thread)
- `page_id` (必填): 粉絲頁 ID
- `post_text` (必填): 貼文內容 (最多 2000 字)
- `post_image` (選填): 貼文圖片 (jpeg, png, jpg, gif, 最大 10MB)
- `post_video` (選填): 貼文影片 (mp4, avi, mov, wmv, 最大 100MB)
- `post_at` (必填): 發送時間 (格式: Y-m-d H:i:s, 台灣時區)
- `status` (選填): 貼文狀態 (1: 排程中, 2: 已發佈, 3: 已下架)

#### 回應範例
```json
{
    "success": true,
    "message": "貼文建立成功",
    "data": {
        "id": 1,
        "member_id": 1,
        "platform": 1,
        "page_id": 123456789,
        "post_text": "這是我的貼文內容",
        "post_image": "post_images/1/uuid.jpg",
        "post_video": null,
        "status": 1,
        "post_image_url": "http://localhost/storage/post_images/1/uuid.jpg",
        "created_at": "2025-01-03T10:00:00.000000Z",
        "updated_at": "2025-01-03T10:00:00.000000Z"
    }
}
```

### 2. 取得貼文列表
**GET** `/api/v1/user/posts`

#### 查詢參數
- `page` (選填): 頁碼 (預設: 1)
- `limit` (選填): 每頁筆數 (預設: 10, 最大: 100)
- `status` (選填): 貼文狀態篩選

#### 回應範例
```json
{
    "success": true,
    "message": "取得貼文列表成功",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "member_id": 1,
                "platform": 1,
                "page_id": 123456789,
                "post_text": "這是我的貼文內容",
                "post_image": "post_images/1/uuid.jpg",
                "post_video": null,
                "status": 1,
                "post_image_url": "http://localhost/storage/post_images/1/uuid.jpg",
                "created_at": "2025-01-03T10:00:00.000000Z",
                "updated_at": "2025-01-03T10:00:00.000000Z"
            }
        ],
        "first_page_url": "http://localhost/api/v1/user/posts?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost/api/v1/user/posts?page=1",
        "links": [...],
        "next_page_url": null,
        "path": "http://localhost/api/v1/user/posts",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

### 3. 取得單一貼文
**GET** `/api/v1/user/posts/{id}`

#### 回應範例
```json
{
    "success": true,
    "message": "取得貼文成功",
    "data": {
        "id": 1,
        "member_id": 1,
        "platform": 1,
        "page_id": 123456789,
        "post_text": "這是我的貼文內容",
        "post_image": "post_images/1/uuid.jpg",
        "post_video": null,
        "status": 1,
        "post_image_url": "http://localhost/storage/post_images/1/uuid.jpg",
        "created_at": "2025-01-03T10:00:00.000000Z",
        "updated_at": "2025-01-03T10:00:00.000000Z"
    }
}
```

### 4. 更新貼文
**PUT** `/api/v1/user/posts/{id}`

#### 請求參數
- `platform` (選填): 平台類型 (1: Facebook, 2: Thread)
- `page_id` (選填): 粉絲頁 ID
- `post_text` (選填): 貼文內容 (最多 2000 字)
- `post_image` (選填): 貼文圖片 (jpeg, png, jpg, gif, 最大 10MB)
- `post_video` (選填): 貼文影片 (mp4, avi, mov, wmv, 最大 100MB)
- `post_at` (選填): 發送時間 (格式: Y-m-d H:i:s, 台灣時區)
- `status` (選填): 貼文狀態 (1: 排程中, 2: 已發佈, 3: 已下架)

#### 回應範例
```json
{
    "success": true,
    "message": "貼文更新成功",
    "data": {
        "id": 1,
        "member_id": 1,
        "platform": 1,
        "page_id": 123456789,
        "post_text": "更新後的貼文內容",
        "post_image": "post_images/1/new-uuid.jpg",
        "post_video": null,
        "status": 2,
        "post_image_url": "http://localhost/storage/post_images/1/new-uuid.jpg",
        "created_at": "2025-01-03T10:00:00.000000Z",
        "updated_at": "2025-01-03T11:00:00.000000Z"
    }
}
```

### 5. 刪除貼文
**DELETE** `/api/v1/user/posts/{id}`

#### 回應範例
```json
{
    "success": true,
    "message": "貼文刪除成功"
}
```

## 錯誤回應

### 404 錯誤
```json
{
    "success": false,
    "message": "貼文不存在"
}
```

### 500 錯誤
```json
{
    "success": false,
    "message": "貼文建立失敗",
    "error": "錯誤訊息"
}
```

### 驗證錯誤
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "post_text": [
            "貼文內容為必填欄位"
        ]
    }
}
```

### 403 錯誤 (已發佈貼文無法更新)
```json
{
    "success": false,
    "message": "已發佈的貼文無法修改"
}
```

## 檔案儲存

### 圖片檔案
- 儲存路徑: `storage/app/public/post_images/{post_id}/{uuid}.{extension}`
- 支援格式: jpeg, png, jpg, gif
- 最大大小: 10MB

### 影片檔案
- 儲存路徑: `storage/app/public/post_videos/{post_id}/{uuid}.{extension}`
- 支援格式: mp4, avi, mov, wmv
- 最大大小: 100MB

### 檔案 URL
- 圖片 URL: `{domain}/storage/post_images/{post_id}/{filename}`
- 影片 URL: `{domain}/storage/post_videos/{post_id}/{filename}`

## 注意事項

1. 所有 API 都需要會員認證
2. 會員只能操作自己的貼文
3. 檔案上傳時會自動產生 UUID 檔名
4. 更新貼文時，如果上傳新檔案，舊檔案會被自動刪除
5. 刪除貼文時，相關的圖片和影片檔案也會被刪除
6. 貼文使用軟刪除，可以在資料庫中恢復
7. **狀態邏輯**:
   - 建立貼文時，無論是否有檔案上傳，狀態都統一設為 `1` (排程中)
   - 更新貼文時，不再因為重新上傳檔案而改變狀態
8. **已發佈狀態限制**:
   - 當貼文狀態為 `2` (已發佈) 時，不允許更新貼文
   - 嘗試更新已發佈的貼文會返回 HTTP 403 錯誤
9. **post_at 欄位**: 必填，格式為 "Y-m-d H:i:s"，直接儲存台灣時區時間
