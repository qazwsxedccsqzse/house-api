# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test tests/Feature/               # Feature tests
php artisan test tests/Unit/                  # Unit tests

# Run specific test files
php artisan test tests/Feature/AdminServiceTest.php
php artisan test tests/Feature/AuthControllerTest.php
php artisan test tests/Unit/LoginServiceTest.php

# Run tests with coverage
php artisan test --coverage

# Run specific test methods
php artisan test --filter="test_signin_with_valid_credentials"
```

### Development Server
```bash
# Start development environment (includes server, queue, logs, and vite)
composer run dev

# Individual services
php artisan serve                    # Development server
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0        # Real-time logs
npm run dev                          # Vite development server
```

### Database Operations
```bash
php artisan migrate        # Run migrations
php artisan db:seed        # Run seeders
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

### Code Quality
```bash
# Laravel Pint (code formatting)
./vendor/bin/pint

# Clear caches for testing
php artisan config:clear
```

## Architecture Overview

This is a Laravel 11 API application implementing a Role-Based Access Control (RBAC) system for admin management and LINE Login integration with PKCE support. The architecture follows Laravel conventions with a clean separation of concerns and proper abstraction layers.

### Key Architectural Patterns

**Repository Pattern**: Data access is abstracted through repositories (`app/Repositories/`). All database operations go through repository classes rather than directly using models in controllers or services.

**Service Layer**: Business logic is encapsulated in service classes (`app/Services/`). Controllers delegate complex operations to services, which in turn use repositories for data access.

**Custom Authentication**: Uses Redis-based token authentication instead of Laravel's default authentication. Tokens are stored with prefixes:
- `admin:token:{token}` → stores admin ID
- `admin:id:{admin_id}` → stores complete admin data as JSON

**Route Structure**:
- Admin API routes are versioned and prefixed with `/api/v1/admin`
- User API routes are versioned and prefixed with `/api/v1/user`
- Routes are configured in `bootstrap/app.php` using separate `withRouting()` calls
- `routes/api.php` → `/api/v1/admin/*` (admin management)
- `routes/api_user.php` → `/api/v1/user/*` (user/customer APIs)

**Social Integration Layer**:
- `app/Foundations/Social/` contains third-party service integrations
- LINE Login integration with PKCE (Proof Key for Code Exchange) support
- Modular design for easy addition of other social providers

### Core Components

**Authentication Flow**:
1. Admin signs in via `AuthController::signin`
2. `AdminService::signin` validates credentials and generates token
3. Token stored in Redis with 30-day expiration
4. `AdminTokenMiddleware` validates tokens on protected routes

**Permission System**:
- Admins have Roles, Roles have Permissions
- Many-to-many relationships: Admin ↔ Role ↔ Permission
- Permissions are loaded and returned during authentication

**Redis Integration**:
- Uses custom `RedisHelper` class (`app/Foundations/RedisHelper.php`)
- Multiple Redis databases: default (0), cache (1), admin (2)
- Configured in `config/database.php` with environment variables
- PKCE code verifier and challenge caching for LINE Login

**LINE Login PKCE Flow**:
1. Generate code verifier and challenge via `LoginService`
2. Store in Redis with 10-minute expiration
3. Exchange authorization code for access token
4. Retrieve user profile from LINE API
5. Clean up code verifier after successful authentication

### Key Models & Relationships
- `Admin` → `roles()` (many-to-many)
- `Role` → `permissions()` (many-to-many) 
- `Member` → user/customer model
- `Notice` → announcements
- `Notification` → admin notifications

### Testing Structure
- **Feature Tests**: Full API endpoint testing with database (7 test files)
- **Unit Tests**: Individual service and component testing (7 test files including LoginServiceTest)
- Uses SQLite in-memory database for testing
- Comprehensive test coverage for authentication, permissions, CRUD operations, and LINE Login
- Mock objects for external dependencies (Redis, HTTP clients)

### Request Validation
All API requests use Form Request classes (`app/Http/Requests/Admin/`) for validation. This provides centralized validation logic and automatic error responses.

### API Endpoints

#### Admin API (`/api/v1/admin`)
**Authentication**:
- `POST /api/v1/admin/auth/signin` - Admin login
- `POST /api/v1/admin/auth/logout` - Admin logout (requires token)

**Admin Management**:
- `GET /api/v1/admin/admins` - List admins
- `POST /api/v1/admin/admins` - Create admin
- `PUT /api/v1/admin/admins/{id}` - Update admin
- `DELETE /api/v1/admin/admins/{id}` - Delete admin
- `PUT /api/v1/admin/admins/{id}/roles` - Assign roles to admin

**Role & Permission Management**:
- `GET /api/v1/admin/roles` - List roles
- `POST /api/v1/admin/roles` - Create role
- `PUT /api/v1/admin/roles/{id}/permissions` - Assign permissions to role
- `GET /api/v1/admin/permissions` - List permissions

**Other Resources**:
- `GET /api/v1/admin/members` - List members/users
- `GET /api/v1/admin/notices` - List notices
- `GET /api/v1/admin/notifications` - List notifications

#### User API (`/api/v1/user`)
**LINE OAuth Authentication**:
- `GET /api/v1/user/auth/line/code-verifier` - Generate PKCE code verifier and challenge
- `GET /api/v1/user/auth/line/oauth` - LINE OAuth callback handler

**User Profile & Resources**:
- `GET /api/v1/user/profile` - Get user profile (planned)
- `PUT /api/v1/user/profile` - Update user profile (planned)
- `GET /api/v1/user/notices` - Get public notices (planned)
- `GET /api/v1/user/notifications` - Get user notifications (planned)

### Development Guidelines
- Follow PSR-12 coding standards
- Use strict typing: `declare(strict_types=1)`
- Repository pattern for data access
- Service pattern for business logic  
- All comments should be in Traditional Chinese (繁體中文)
- Use Laravel's built-in features and Eloquent ORM
- Implement proper error handling with custom exceptions
- Controller 只能注入 service, 不能注入 repo
- Social integrations should be placed in `app/Foundations/Social/`
- Write comprehensive unit tests for all service classes
- Use mock objects for external dependencies in tests

### Environment Configuration

#### Required Environment Variables
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=house_api
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_ADMIN_DB=2

# LINE Login OAuth
LINE_CLIENT_ID=your_line_client_id
LINE_CLIENT_SECRET=your_line_client_secret
LINE_REDIRECT_URI=http://localhost:8000/api/v1/user/auth/line/oauth
```

#### Development Dependencies
- PHP 8.2+
- Redis server (for caching and PKCE storage)
- MySQL/PostgreSQL database
- Composer for PHP dependencies
- Node.js and npm for frontend assets