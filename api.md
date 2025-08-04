# API 文檔

## 認證相關 API

### 登入

- **端點**: `POST /api/auth/signin`
- **呼叫檔案**: `src/api/services/userService.ts` → `signin()`
- **使用位置**: `src/store/userStore.ts` → `useSignIn()`
- **請求體**:

```json
{
  "username": "string",
  "password": "string"
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "user": {
      "id": "string",
      "username": "string",
      "email": "string",
      "avatar": "string",
      "roles": [
        {
          "id": "string",
          "name": "string",
          "code": "string"
        }
      ],
      "permissions": [
        {
          "id": "string",
          "name": "string",
          "code": "string"
        }
      ],
      "menu": [
        {
          "id": "string",
          "name": "string",
          "path": "string",
          "children": []
        }
      ]
    },
    "accessToken": "string",
    "refreshToken": "string"
  }
}
```

### 註冊

- **端點**: `POST /api/auth/signup`
- **呼叫檔案**: `src/api/services/userService.ts` → `signup()`
- **使用位置**: 登入頁面組件 (待實作)
- **請求體**:

```json
{
  "username": "string",
  "password": "string",
  "email": "string"
}
```

- **響應**: 同登入響應

### 登出

- **端點**: `GET /api/auth/logout`
- **呼叫檔案**: `src/api/services/userService.ts` → `logout()`
- **使用位置**: 用戶操作相關組件 (待實作)
- **響應**:

```json
{
  "status": 0,
  "message": "登出成功",
  "data": null
}
```

### 刷新 Token

- **端點**: `GET /api/auth/refresh`
- **呼叫檔案**: `src/api/services/userService.ts` → `refresh()` (待實作)
- **使用位置**: `src/api/apiClient.ts` → 攔截器自動處理
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "accessToken": "string",
    "refreshToken": "string"
  }
}
```

## 用戶相關 API

### 獲取用戶列表

- **端點**: `GET /api/users`
- **呼叫檔案**: `src/api/services/userService.ts` → `findById()`
- **使用位置**: `src/_mock/handlers/_user.ts` → `userList`
- **響應**:

```json
[
  {
    "fullname": "string",
    "email": "string",
    "avatar": "string",
    "address": "string"
  }
]
```

### 獲取用戶詳情

- **端點**: `GET /api/users/:id`
- **呼叫檔案**: `src/api/services/userService.ts` → `findById()`
- **使用位置**: 用戶詳情頁面 (待實作)
- **響應**:

```json
{
  "id": "string",
  "username": "string",
  "email": "string",
  "avatar": "string",
  "roles": [],
  "permissions": []
}
```

## 菜單相關 API

### 獲取菜單列表

- **端點**: `GET /api/menu`
- **呼叫檔案**: `src/api/services/menuService.ts` → `getMenuList()`
- **使用位置**: `src/main.tsx` → 應用啟動時載入
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": [
    {
      "id": "string",
      "name": "string",
      "path": "string",
      "icon": "string",
      "children": []
    }
  ]
}
```

## 通知相關 API

### 獲取通知列表

- **端點**: `GET /api/notifications`
- **呼叫檔案**: `src/api/services/notificationService.ts` → `getNotifications()`
- **使用位置**: `src/layouts/components/notice.tsx` → `NoticeButton` 和 `NoticeTab`
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "notifications": [
      {
        "id": 1,
        "type": "mention|tags|access|file|article|project",
        "user": "string",
        "action": "string",
        "target": "string",
        "targetType": "string",
        "time": "string",
        "department": "string",
        "message": "string",
        "hasReply": true,
        "hasAvatar": true,
        "hasActions": true,
        "fileName": "string",
        "fileSize": "string",
        "fileType": "string",
        "editedTime": "string",
        "tags": ["string"],
        "meetingTitle": "string",
        "meetingDate": "string",
        "meetingTime": "string",
        "attendees": 0,
        "userName": "string",
        "userEmail": "string",
        "taskTitle": "string",
        "dueDate": "string",
        "assignees": 0,
        "files": [
          {
            "name": "string",
            "type": "string",
            "size": "string"
          }
        ],
        "artworks": [
          {
            "id": "string",
            "title": "string"
          }
        ],
        "hasProfileAction": true
      }
    ],
    "total": 0,
    "unread": 0
  }
}
```

### 標記通知為已讀

- **端點**: `PUT /api/notifications/read/:id`
- **呼叫檔案**: `src/api/services/notificationService.ts` → `markAsRead()`
- **使用位置**: 通知操作組件 (待實作)
- **響應**:

```json
{
  "status": 0,
  "message": "Marked as read successfully",
  "data": null
}
```

### 標記所有通知為已讀

- **端點**: `PUT /api/notifications/read-all`
- **呼叫檔案**: `src/api/services/notificationService.ts` → `markAllAsRead()`
- **使用位置**: `src/layouts/components/notice.tsx` → `NoticeButton` 和 `NoticeTab`
- **響應**:

```json
{
  "status": 0,
  "message": "All notifications marked as read",
  "data": null
}
```

### 歸檔所有通知

- **端點**: `PUT /api/notifications/archive-all`
- **呼叫檔案**: `src/api/services/notificationService.ts` → `archiveAll()`
- **使用位置**: `src/layouts/components/notice.tsx` → `NoticeButton` 和 `NoticeTab`
- **響應**:

```json
{
  "status": 0,
  "message": "All notifications archived",
  "data": null
}
```

## 會員管理 API

### 獲取會員列表

- **端點**: `GET /api/members`
- **呼叫檔案**: `src/api/services/memberService.ts` → `getMembers()` (待實作)
- **使用位置**: 會員列表頁面 (待實作)
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "string",
        "name": "string",
        "email": "string",
        "phone": "string",
        "status": 0,
        "createdAt": "string",
        "updatedAt": "string"
      }
    ],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

### 創建會員

- **端點**: `POST /api/members`
- **呼叫檔案**: `src/api/services/memberService.ts` → `createMember()` (待實作)
- **使用位置**: 會員創建頁面 (待實作)
- **請求體**:

```json
{
  "name": "string",
  "email": "string",
  "phone": "string"
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "會員創建成功",
  "data": {
    "id": "string",
    "name": "string",
    "email": "string",
    "phone": "string",
    "status": 0,
    "createdAt": "string"
  }
}
```

### 更新會員

- **端點**: `PUT /api/members/:id`
- **呼叫檔案**: `src/api/services/memberService.ts` → `updateMember()` (待實作)
- **使用位置**: 會員編輯頁面 (待實作)
- **請求體**: 同創建會員
- **響應**: 同創建會員響應

### 刪除會員

- **端點**: `DELETE /api/members/:id`
- **呼叫檔案**: `src/api/services/memberService.ts` → `deleteMember()` (待實作)
- **使用位置**: 會員列表頁面 (待實作)
- **響應**:

```json
{
  "status": 0,
  "message": "會員刪除成功",
  "data": null
}
```

## 帳務管理 API

### 獲取帳務記錄列表

- **端點**: `GET /api/accounting`
- **呼叫檔案**: `src/api/services/accountingService.ts` → `getAccountingRecords()` (待實作)
- **使用位置**: `src/pages/accounting/index.tsx` → `AccountingListPage`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `memberId`: 會員 ID (可選)
  - `status`: 狀態 (可選)
  - `startDate`: 開始日期 (可選)
  - `endDate`: 結束日期 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "string",
        "memberId": "string",
        "memberName": "string",
        "orderNumber": "string",
        "status": 0,
        "amount": 0,
        "paymentMethod": "string",
        "createdAt": "string"
      }
    ],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

### 創建帳務記錄

- **端點**: `POST /api/accounting`
- **呼叫檔案**: `src/api/services/accountingService.ts` → `createAccountingRecord()` (待實作)
- **使用位置**: 帳務記錄創建頁面 (待實作)
- **請求體**:

```json
{
  "memberId": "string",
  "orderNumber": "string",
  "amount": 0,
  "paymentMethod": "string"
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "帳務記錄創建成功",
  "data": {
    "id": "string",
    "memberId": "string",
    "memberName": "string",
    "orderNumber": "string",
    "status": 0,
    "amount": 0,
    "paymentMethod": "string",
    "createdAt": "string"
  }
}
```

### 更新帳務記錄

- **端點**: `PUT /api/accounting/:id`
- **呼叫檔案**: `src/api/services/accountingService.ts` → `updateAccountingRecord()` (待實作)
- **使用位置**: 帳務記錄編輯頁面 (待實作)
- **請求體**: 同創建帳務記錄
- **響應**: 同創建帳務記錄響應

### 刪除帳務記錄

- **端點**: `DELETE /api/accounting/:id`
- **呼叫檔案**: `src/api/services/accountingService.ts` → `deleteAccountingRecord()` (待實作)
- **使用位置**: 帳務記錄列表頁面 (待實作)
- **響應**:

```json
{
  "status": 0,
  "message": "帳務記錄刪除成功",
  "data": null
}
```

## 公告管理 API

### 獲取公告列表

- **端點**: `GET /api/notices`
- **呼叫檔案**: `src/api/services/noticeService.ts` → `getNotices()`
- **使用位置**: `src/pages/notice/index.tsx` → `NoticeListPage`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "string",
        "title": "string",
        "content": "string",
        "image": "string",
        "status": 0,
        "createdAt": "string",
        "updatedAt": "string",
        "createdBy": "string"
      }
    ],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

### 創建公告

- **端點**: `POST /api/notices`
- **呼叫檔案**: `src/api/services/noticeService.ts` → `createNotice()`
- **使用位置**: `src/pages/notice/create.tsx` → `CreateNoticePage`
- **請求體**:

```json
{
  "title": "string",
  "content": "string",
  "image": "string",
  "status": 0
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "公告創建成功",
  "data": {
    "id": "string",
    "title": "string",
    "content": "string",
    "image": "string",
    "status": 0,
    "createdAt": "string",
    "updatedAt": "string",
    "createdBy": "string"
  }
}
```

### 更新公告

- **端點**: `PUT /api/notices/:id`
- **呼叫檔案**: `src/api/services/noticeService.ts` → `updateNotice()`
- **使用位置**: `src/pages/notice/edit.tsx` → `EditNoticePage`
- **請求體**: 同創建公告
- **響應**: 同創建公告響應

### 刪除公告

- **端點**: `DELETE /api/notices/:id`
- **呼叫檔案**: `src/api/services/noticeService.ts` → `deleteNotice()`
- **使用位置**: `src/pages/notice/index.tsx` → `NoticeListPage`
- **響應**:

```json
{
  "status": 0,
  "message": "公告刪除成功",
  "data": null
}
```

## 會員管理 API

### 獲取會員列表

- **端點**: `GET /api/members`
- **呼叫檔案**: `src/api/services/memberService.ts` → `getMembers()`
- **使用位置**: `src/pages/member/index.tsx` → `MemberListPage`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
  - `startDate`: 起始日期 (可選)
  - `endDate`: 結束日期 (可選)
  - `category`: 篩選類別 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "string",
        "name": "string",
        "lineId": "string",
        "joinPlan": "string",
        "purchaseDate": "string",
        "bindingStatus": "string",
        "status": 0,
        "createdAt": "string"
      }
    ],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

### 更新會員

- **端點**: `PUT /api/members/:id`
- **呼叫檔案**: `src/api/services/memberService.ts` → `updateMember()`
- **使用位置**: `src/pages/member/edit.tsx` → `EditMemberPage`
- **請求體**:

```json
{
  "name": "string",
  "lineId": "string",
  "joinPlan": "string",
  "purchaseDate": "string",
  "bindingStatus": "string",
  "status": 0
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "會員更新成功",
  "data": {
    "id": "string",
    "name": "string",
    "lineId": "string",
    "joinPlan": "string",
    "purchaseDate": "string",
    "bindingStatus": "string",
    "status": 0,
    "createdAt": "string"
  }
}
```

## 權限管理 API

### 獲取權限列表

- **端點**: `GET /api/permissions`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `getPermissions()`
- **使用位置**:
  - `src/pages/management/system/permission/index.tsx` → `PermissionPage`
  - `src/hooks/use-permissions.ts` → `usePermissions()`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "string",
        "name": "公告管理讀取",
        "code": "notice:read",
        "description": "查看公告列表和詳情",
        "type": 1,
        "status": 1,
        "createdAt": "string"
      }
    ],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

### 創建權限

- **端點**: `POST /api/permissions`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `createPermission()`
- **使用位置**: `src/pages/management/system/permission/permission-modal.tsx` → `PermissionModal`
- **請求體**:

```json
{
  "name": "string",
  "code": "string",
  "description": "string",
  "status": 1
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "權限創建成功",
  "data": {
    "id": "string",
    "name": "string",
    "code": "string",
    "description": "string",
    "type": 1,
    "status": 1,
    "createdAt": "string"
  }
}
```

### 更新權限

- **端點**: `PUT /api/permissions/:id`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `updatePermission()`
- **使用位置**: `src/pages/management/system/permission/edit.tsx` → `EditPermissionPage`
- **請求體**:

```json
{
  "name": "string",
  "code": "string",
  "description": "string",
  "status": 1
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "權限更新成功",
  "data": {
    "id": "string",
    "name": "string",
    "code": "string",
    "description": "string",
    "type": 1,
    "status": 1,
    "createdAt": "string"
  }
}
```

### 刪除權限

- **端點**: `DELETE /api/permissions/:id`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `deletePermission()`
- **使用位置**: `src/pages/management/system/permission/index.tsx` → `PermissionPage`
- **響應**:

```json
{
  "status": 0,
  "message": "權限刪除成功",
  "data": null
}
```

## 角色管理 API

### 獲取角色列表

- **端點**: `GET /api/roles`
- **呼叫檔案**: `src/api/services/roleService.ts` → `getRoles()`
- **使用位置**: `src/pages/management/system/role/index.tsx` → `RolePage`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "1",
        "name": "超級管理員",
        "code": "super-admin",
        "description": "擁有所有權限",
        "status": 1,
        "permissions": [
          "notice:read",
          "notice:edit",
          "member:read",
          "member:edit",
          "order:read",
          "permission:read",
          "permission:edit",
          "role:read",
          "role:edit",
          "admin:read",
          "admin:edit"
        ],
        "createdAt": "2024-01-01 00:00:00"
      },
      {
        "id": "2",
        "name": "內容管理員",
        "code": "content-admin",
        "description": "擁有所有跟公告相關的權限",
        "status": 1,
        "permissions": ["notice:read", "notice:edit"],
        "createdAt": "2024-01-01 00:00:00"
      },
      {
        "id": "3",
        "name": "客服人員",
        "code": "customer-service",
        "description": "擁有所有跟會員相關的功能",
        "status": 1,
        "permissions": ["member:read", "member:edit"],
        "createdAt": "2024-01-01 00:00:00"
      }
    ],
    "total": 3,
    "page": 1,
    "limit": 10
  }
}
```

### 創建角色

- **端點**: `POST /api/roles`
- **呼叫檔案**: `src/api/services/roleService.ts` → `createRole()`
- **使用位置**: `src/pages/management/system/role/role-modal.tsx` → `RoleModal`
- **請求體**:

```json
{
  "name": "string",
  "code": "string",
  "description": "string",
  "status": 1,
  "permissions": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "角色創建成功",
  "data": {
    "id": "string",
    "name": "string",
    "code": "string",
    "description": "string",
    "status": 1,
    "permissions": ["string"],
    "createdAt": "string"
  }
}
```

### 更新角色

- **端點**: `PUT /api/roles/:id`
- **呼叫檔案**: `src/api/services/roleService.ts` → `updateRole()`
- **使用位置**: `src/pages/management/system/role/role-modal.tsx` → `RoleModal`
- **請求體**: 同創建角色
- **響應**: 同創建角色響應

### 刪除角色

- **端點**: `DELETE /api/roles/:id`
- **呼叫檔案**: `src/api/services/roleService.ts` → `deleteRole()`
- **使用位置**: `src/pages/management/system/role/index.tsx` → `RolePage`
- **響應**:

```json
{
  "status": 0,
  "message": "角色刪除成功",
  "data": null
}
```

### 分配角色權限

- **端點**: `PUT /api/roles/:id/permissions`
- **呼叫檔案**: `src/api/services/roleService.ts` → `assignPermissions()`
- **使用位置**: `src/pages/management/system/role/role-modal.tsx` → `RoleModal`
- **請求體**:

```json
{
  "permissions": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "權限分配成功",
  "data": null
}
```

## 管理員管理 API

### 獲取管理員列表

- **端點**: `GET /api/admins`
- **呼叫檔案**: `src/api/services/adminService.ts` → `getAdmins()`
- **使用位置**: `src/pages/management/system/admin/index.tsx` → `AdminPage`
- **查詢參數**:
  - `page`: 頁碼 (可選)
  - `limit`: 每頁數量 (可選)
  - `search`: 搜尋關鍵字 (可選)
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [
      {
        "id": "1",
        "username": "admin",
        "name": "系統管理員",
        "email": "admin@example.com",
        "status": 1,
        "roles": ["super-admin"],
        "createdAt": "2024-01-01 00:00:00"
      },
      {
        "id": "2",
        "username": "content-admin",
        "name": "內容管理員",
        "email": "content@example.com",
        "status": 1,
        "roles": ["content-admin"],
        "createdAt": "2024-01-01 00:00:00"
      },
      {
        "id": "3",
        "username": "customer-service",
        "name": "客服人員",
        "email": "service@example.com",
        "status": 1,
        "roles": ["customer-service"],
        "createdAt": "2024-01-01 00:00:00"
      }
    ],
    "total": 3,
    "page": 1,
    "limit": 10
  }
}
```

### 創建管理員

- **端點**: `POST /api/admins`
- **呼叫檔案**: `src/api/services/adminService.ts` → `createAdmin()`
- **使用位置**: `src/pages/management/system/admin/admin-modal.tsx` → `AdminModal`
- **請求體**:

```json
{
  "username": "string",
  "password": "string",
  "name": "string",
  "email": "string",
  "status": 1,
  "roles": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "管理員創建成功",
  "data": {
    "id": "string",
    "username": "string",
    "name": "string",
    "email": "string",
    "status": 1,
    "roles": ["string"],
    "createdAt": "string"
  }
}
```

### 更新管理員

- **端點**: `PUT /api/admins/:id`
- **呼叫檔案**: `src/api/services/adminService.ts` → `updateAdmin()`
- **使用位置**: `src/pages/management/system/admin/admin-modal.tsx` → `AdminModal`
- **請求體**:

```json
{
  "username": "string",
  "password": "string (可選)",
  "name": "string",
  "email": "string",
  "status": 1,
  "roles": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "管理員更新成功",
  "data": {
    "id": "string",
    "username": "string",
    "name": "string",
    "email": "string",
    "status": 1,
    "roles": ["string"],
    "updatedAt": "string"
  }
}
```

### 刪除管理員

- **端點**: `DELETE /api/admins/:id`
- **呼叫檔案**: `src/api/services/adminService.ts` → `deleteAdmin()`
- **使用位置**: `src/pages/management/system/admin/index.tsx` → `AdminPage`
- **響應**:

```json
{
  "status": 0,
  "message": "管理員刪除成功",
  "data": null
}
```

### 分配管理員角色

- **端點**: `PUT /api/admins/:id/roles`
- **呼叫檔案**: `src/api/services/adminService.ts` → `assignRoles()`
- **使用位置**: `src/pages/management/system/admin/admin-modal.tsx` → `AdminModal`
- **請求體**:

```json
{
  "roles": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "角色分配成功",
  "data": null
}
```

## 系統管理 API

### 獲取權限列表

- **端點**: `GET /api/permissions`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `getPermissions()` (待實作)
- **使用位置**: `src/pages/management/system/permission/index.tsx` → `PermissionPage`
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": [
    {
      "id": "string",
      "name": "string",
      "code": "string",
      "type": 0,
      "status": 0,
      "createdAt": "string"
    }
  ]
}
```

### 創建權限

- **端點**: `POST /api/permissions`
- **呼叫檔案**: `src/api/services/permissionService.ts` → `createPermission()` (待實作)
- **使用位置**: `src/pages/management/system/permission/permission-modal.tsx` → `PermissionModal`
- **請求體**:

```json
{
  "name": "string",
  "code": "string",
  "type": 0
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "權限創建成功",
  "data": {
    "id": "string",
    "name": "string",
    "code": "string",
    "type": 0,
    "status": 0,
    "createdAt": "string"
  }
}
```

### 獲取角色列表

- **端點**: `GET /api/roles`
- **呼叫檔案**: `src/api/services/roleService.ts` → `getRoles()` (待實作)
- **使用位置**: `src/pages/management/system/role/index.tsx` → `RolePage`
- **響應**:

```json
{
  "status": 0,
  "message": "",
  "data": [
    {
      "id": "string",
      "name": "string",
      "code": "string",
      "status": 0,
      "permissions": [],
      "createdAt": "string"
    }
  ]
}
```

### 創建角色

- **端點**: `POST /api/roles`
- **呼叫檔案**: `src/api/services/roleService.ts` → `createRole()` (待實作)
- **使用位置**: `src/pages/management/system/role/role-modal.tsx` → `RoleModal`
- **請求體**:

```json
{
  "name": "string",
  "code": "string",
  "permissions": ["string"]
}
```

- **響應**:

```json
{
  "status": 0,
  "message": "角色創建成功",
  "data": {
    "id": "string",
    "name": "string",
    "code": "string",
    "status": 0,
    "permissions": [],
    "createdAt": "string"
  }
}
```

## 通用響應格式

### 成功響應

```json
{
  "status": 0,
  "message": "操作成功",
  "data": {}
}
```

### 錯誤響應

```json
{
  "status": -1,
  "message": "錯誤訊息",
  "data": null
}
```

### 分頁響應

```json
{
  "status": 0,
  "message": "",
  "data": {
    "list": [],
    "total": 0,
    "page": 1,
    "limit": 10
  }
}
```

## 狀態碼說明

- `0`: 成功
- `-1`: 一般錯誤
- `401`: 未授權 (Token 過期)
- `403`: 禁止訪問
- `404`: 資源不存在
- `500`: 伺服器錯誤

## 枚舉值說明

### AccountingStatus (帳務狀態)

- `0`: 未付款 (UNPAID)
- `1`: 已付款 (PAID)
- `2`: 已退款 (REFUNDED)

### BasicStatus (基本狀態)

- `0`: 停用 (DISABLE)
- `1`: 啟用 (ENABLE)

### PermissionType (權限類型)

- `0`: 群組 (GROUP)
- `1`: 目錄 (CATALOGUE)
- `2`: 菜單 (MENU)
- `3`: 元件 (COMPONENT)
