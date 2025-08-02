# 權限系統說明

## 概述

這個權限系統基於 Laravel 的 RBAC (Role-Based Access Control) 模型，包含以下核心組件：

- **Admin**: 管理員模型
- **Role**: 角色模型  
- **Permission**: 權限模型
- **AdminRole**: 管理員-角色關聯表
- **RolePermission**: 角色-權限關聯表

## 資料庫結構

### 表結構

1. **admins** - 管理員表
   - `id`: 主鍵
   - `name`: 管理員名稱
   - `email`: 管理員信箱 (唯一)
   - `password`: 密碼
   - `status`: 狀態 (1=啟用, 0=停用)

2. **roles** - 角色表
   - `id`: 主鍵
   - `name`: 角色名稱
   - `code`: 角色代碼 (唯一)
   - `description`: 角色描述
   - `status`: 狀態 (1=啟用, 0=停用)

3. **permissions** - 權限表
   - `id`: 主鍵
   - `name`: 權限名稱
   - `code`: 權限代碼 (唯一)
   - `description`: 權限描述
   - `status`: 狀態 (1=啟用, 0=停用)

4. **admin_roles** - 管理員角色關聯表
   - `admin_id`: 管理員ID (外鍵)
   - `role_id`: 角色ID (外鍵)

5. **role_permissions** - 角色權限關聯表
   - `role_id`: 角色ID (外鍵)
   - `permission_id`: 權限ID (外鍵)

## 預設權限

系統預設包含以下 11 個權限：

| ID | 權限名稱 | 權限代碼 | 描述 |
|----|----------|----------|------|
| 1 | 公告管理讀取 | `notice:read` | 查看公告列表和詳情 |
| 2 | 公告管理編輯 | `notice:edit` | 創建、編輯、刪除公告 |
| 3 | 會員管理讀取 | `member:read` | 查看會員列表和詳情 |
| 4 | 會員管理編輯 | `member:edit` | 編輯會員資訊 |
| 5 | 帳務管理讀取 | `order:read` | 查看帳務列表和詳情 |
| 6 | 權限列表讀取 | `permission:read` | 查看權限列表 |
| 7 | 權限列表編輯 | `permission:edit` | 創建、編輯、刪除權限 |
| 8 | 角色列表讀取 | `role:read` | 查看角色列表 |
| 9 | 角色列表編輯 | `role:edit` | 創建、編輯、刪除角色 |
| 10 | 管理員列表讀取 | `admin:read` | 查看管理員列表 |
| 11 | 管理員列表編輯 | `admin:edit` | 創建、編輯、刪除管理員 |

## 預設角色

系統預設包含以下 3 個角色：

### 1. 超級管理員 (`super-admin`)
- **描述**: 擁有所有權限
- **權限**: 所有 11 個權限
  - `notice:read`, `notice:edit`
  - `member:read`, `member:edit`
  - `order:read`
  - `permission:read`, `permission:edit`
  - `role:read`, `role:edit`
  - `admin:read`, `admin:edit`

### 2. 內容管理員 (`content-admin`)
- **描述**: 擁有所有跟公告相關的權限
- **權限**: 2 個權限
  - `notice:read` - 查看公告列表和詳情
  - `notice:edit` - 創建、編輯、刪除公告

### 3. 客服人員 (`customer-service`)
- **描述**: 擁有所有跟會員相關的功能
- **權限**: 2 個權限
  - `member:read` - 查看會員列表和詳情
  - `member:edit` - 編輯會員資訊

## 使用方法

### 1. 檢查權限

```php
// 檢查單一權限
if ($admin->hasPermission('notice:read')) {
    // 允許查看公告
}

// 檢查多個權限中的任一
if ($admin->hasAnyPermission(['notice:read', 'notice:edit'])) {
    // 允許查看或編輯公告
}

// 檢查是否擁有所有指定權限
if ($admin->hasAllPermissions(['notice:read', 'notice:edit'])) {
    // 同時擁有查看和編輯權限
}
```

### 2. 分配角色給管理員

```php
$admin = Admin::find(1);
$role = Role::where('code', 'super-admin')->first();

// 分配單一角色
$admin->roles()->attach($role->id);

// 分配多個角色
$admin->roles()->attach([$role1->id, $role2->id]);

// 同步角色 (會移除舊的分配)
$admin->roles()->sync([$role->id]);
```

### 3. 分配權限給角色

```php
$role = Role::find(1);
$permission = Permission::where('code', 'notice:read')->first();

// 分配單一權限
$role->permissions()->attach($permission->id);

// 分配多個權限
$role->permissions()->attach([$permission1->id, $permission2->id]);

// 同步權限
$role->permissions()->sync([$permission->id]);
```

### 4. 獲取管理員的所有權限

```php
$admin = Admin::find(1);

// 獲取所有角色
$roles = $admin->roles;

// 獲取所有權限 (通過角色)
$permissions = $admin->roles()
    ->with('permissions')
    ->get()
    ->flatMap(function ($role) {
        return $role->permissions;
    })
    ->unique('id');
```

## 運行 Seeder

```bash
# 運行所有 seeder
php artisan db:seed

# 只運行特定 seeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=AdminSeeder
```

## 預設管理員帳號

系統會自動建立一個超級管理員帳號：

- **Email**: admin@example.com
- **Password**: password
- **角色**: 超級管理員 (擁有所有權限)

## 測試

運行測試來驗證權限系統：

```bash
php artisan test tests/Feature/PermissionTest.php
```

測試包含以下功能：
- 權限 seeder 正確建立權限
- 角色 seeder 正確建立角色
- 角色權限 seeder 正確分配權限
- 管理員權限檢查功能
- 不同角色的權限限制驗證

## 注意事項

1. 權限檢查是基於角色的，管理員必須先被分配角色，然後角色再被分配權限
2. 使用 `updateOrCreate` 方法可以避免重複插入資料
3. 所有外鍵關係都設置了 `onDelete('cascade')`，確保資料一致性
4. 使用唯一索引防止重複分配角色和權限
5. 角色權限分配使用 `sync()` 方法，會自動移除舊的分配並添加新的分配 
