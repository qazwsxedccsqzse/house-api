# House API

基於 Laravel 11 開發的管理員認證與權限管理 API 系統。

## 專案架構

```
house-api/
├── app/                          # 應用程式核心目錄
│   ├── Exceptions/               # 自定義異常處理
│   │   └── CustomException.php   # 自定義異常類別
│   ├── Foundations/              # 基礎設施層
│   │   └── RedisHelper.php       # Redis 操作輔助類別
│   ├── Http/                     # HTTP 層
│   │   ├── Controllers/          # 控制器
│   │   │   └── Admin/           # 管理員相關控制器
│   │   │       └── AuthController.php
│   │   ├── Middlewares/         # 中間件
│   │   │   └── AdminTokenMiddleware.php
│   │   └── Requests/            # 請求驗證
│   │       └── Admin/
│   │           └── AdminSignInRequest.php
│   ├── Models/                   # 資料模型
│   │   ├── Admin.php            # 管理員模型
│   │   ├── Permission.php       # 權限模型
│   │   └── Role.php             # 角色模型
│   ├── Repositories/             # 資料存取層
│   │   └── AdminRepo.php        # 管理員資料存取
│   └── Services/                 # 業務邏輯層
│       └── AdminService.php     # 管理員業務邏輯
├── bootstrap/                    # 應用程式啟動配置
│   └── app.php                  # Laravel 11 配置
├── config/                       # 配置檔案
│   ├── app.php                  # 應用程式配置
│   ├── auth.php                 # 認證配置
│   ├── database.php             # 資料庫配置
│   └── ...
├── database/                     # 資料庫相關
│   ├── factories/               # 模型工廠
│   ├── migrations/              # 資料庫遷移
│   └── seeders/                 # 資料填充
├── routes/                       # 路由定義
│   └── api.php                  # API 路由
├── storage/                      # 檔案儲存
├── tests/                        # 測試檔案
│   ├── Feature/                 # 功能測試
│   │   ├── AdminServiceTest.php
│   │   ├── AdminTokenMiddlewareTest.php
│   │   ├── AuthControllerTest.php
│   │   └── PermissionTest.php
│   └── Unit/                    # 單元測試
└── vendor/                       # Composer 依賴
```

## 目錄職責說明

### `app/` - 應用程式核心
- **Exceptions/**: 自定義異常處理類別
- **Foundations/**: 基礎設施層，包含 Redis 等外部服務的封裝
- **Http/**: HTTP 請求處理層
  - **Controllers/**: 控制器，處理 HTTP 請求並返回響應
  - **Middlewares/**: 中間件，處理請求過濾和驗證
  - **Requests/**: 請求驗證類別
- **Models/**: Eloquent 模型，定義資料結構和關聯
- **Repositories/**: 資料存取層，封裝資料庫操作
- **Services/**: 業務邏輯層，處理複雜的業務邏輯

### `bootstrap/` - 應用程式啟動
- **app.php**: Laravel 11 的應用程式配置，包含中間件和異常處理註冊

### `config/` - 配置檔案
- 包含所有應用程式的配置設定

### `database/` - 資料庫相關
- **factories/**: 模型工廠，用於測試資料生成
- **migrations/**: 資料庫結構定義
- **seeders/**: 初始資料填充

### `routes/` - 路由定義
- **api.php**: API 路由定義

### `tests/` - 測試檔案
- **Feature/**: 功能測試，測試完整的 API 端點
- **Unit/**: 單元測試，測試個別類別和方法

## Redis Helper 連線配置

### 環境變數設定
在 `.env` 檔案中設定以下變數：

```env
# Redis 基本配置
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_USERNAME=null

# Redis 資料庫配置
REDIS_DB=0          # 預設資料庫
REDIS_CACHE_DB=1    # 快取資料庫
REDIS_ADMIN_DB=2    # 管理員相關資料庫

# Redis 客戶端
REDIS_CLIENT=phpredis

# Redis 叢集配置（可選）
REDIS_CLUSTER=redis
REDIS_PERSISTENT=false
```

### 資料庫配置
在 `config/database.php` 中已配置：

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
    
    'admin-redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_ADMIN_DB', '2'),
    ],
],
```

### RedisHelper 使用方式
```php
use App\Foundations\RedisHelper;

// 使用預設連線
$redis = new RedisHelper('cache');

// 使用管理員專用連線
$redis = new RedisHelper('admin-redis');
```

## Admin Token Prefix

系統中使用的 Redis Key 前綴：

### Token 相關
- `admin:token:{token}` - 儲存 token 對應的 admin ID
- `admin:id:{admin_id}` - 儲存 admin 的完整資料（JSON 格式）

### 範例
```
admin:token:abc123def456... -> 1
admin:id:1 -> {"id":1,"username":"admin","email":"admin@example.com",...}
```

## 單元測試執行指令

### 執行所有測試
```bash
php artisan test
```

### 執行特定測試檔案
```bash
# 管理員服務測試
php artisan test tests/Feature/AdminServiceTest.php

# 認證控制器測試
php artisan test tests/Feature/AuthControllerTest.php

# Admin Token 中間件測試
php artisan test tests/Feature/AdminTokenMiddlewareTest.php

# 權限系統測試
php artisan test tests/Feature/PermissionTest.php
```

### 執行特定測試方法
```bash
# 執行特定測試方法
php artisan test --filter="test_signin_with_valid_credentials"

# 執行包含特定關鍵字的測試
php artisan test --filter="signin"
```

### 測試覆蓋率報告
```bash
# 生成測試覆蓋率報告
php artisan test --coverage

# 生成 HTML 覆蓋率報告
php artisan test --coverage-html=coverage/
```

### 測試選項
```bash
# 詳細輸出
php artisan test --verbose

# 停止在第一次失敗
php artisan test --stop-on-failure

# 並行執行測試
php artisan test --parallel
```

## API 端點

### 認證相關
- `POST /api/auth/signin` - 管理員登入
- `POST /api/auth/logout` - 管理員登出（需要 Bearer Token）

### 請求範例
```bash
# 登入
curl -X POST http://localhost:8000/api/auth/signin \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# 登出
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer your_access_token"
```

## 開發環境設定

### 安裝依賴
```bash
composer install
npm install
```

### 環境設定
```bash
cp .env.example .env
php artisan key:generate
```

### 資料庫設定
```bash
php artisan migrate
php artisan db:seed
```

### 啟動開發伺服器
```bash
php artisan serve
```

## 技術棧

- **框架**: Laravel 11
- **資料庫**: MySQL/PostgreSQL
- **快取**: Redis
- **測試**: PHPUnit
- **認證**: 自定義 Token 系統
- **權限**: RBAC (Role-Based Access Control)
