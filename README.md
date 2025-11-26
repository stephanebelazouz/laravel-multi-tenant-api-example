# 🏗️ Laravel Multi-Tenant API Example

⚠️ This project is a study prototype not a ready to use repository. It is only a proposed implementation meant for experimentation and learning.


[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-316192?style=flat&logo=postgresql)](https://postgresql.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Architecture](#-architecture)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Roles & Permissions](#-roles--permissions)
- [API Testing](#-api-testing)
- [Project Structure](#-project-structure)
- [API Documentation](#-api-documentation)
- [Security](#-security)
- [Performance](#-performance)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Changelog](#-changelog)
- [License](#-license)

## 🎯 Objectives

Provides a solid foundation for building multi-tenant SaaS API with Laravel. 
It uses the **"database per tenant"** approach for complete data isolation, particularly suitable for applications requiring strict compliance (HIPAA, GDPR).

### Why this starter?

- ✅ **Clean Architecture**: Actions, Controllers, Services properly separated
- ✅ **Multi-tenant Ready**: Separate database per tenant with complete isolation
- ✅ **Role-Based Access Control**: Built-in permission system using PHP Enums
- ✅ **Robust Authentication**: Sanctum for API tokens
- ✅ **Tests Included**: Complete Bruno API collection
- ✅ **Best Practices**: PSR-12, validation, logging
- ✅ **Production Ready**: Error handling, DB transactions

## ✨ Features

### 🔐 Authentication

- Central authentication system (platform administrators)
- Per-tenant authentication (organization users)
- JWT tokens via Laravel Sanctum
- Session and refresh token management

### 🏢 Multi-Tenant Management

- **Complete Isolation**: Each tenant has its own PostgreSQL database with separate users
- **Automatic Provisioning**: Database creation and migration on tenant creation
- **Header-based Identification**: `X-Tenant` for API calls
- **Secure**: Users are isolated per tenant - no cross-tenant access
- **Scalable**: Optimized for thousands of tenants

### 👥 User Management

- **Central users**: Platform administrators with role-based permissions
- **Tenant users**: Organization members isolated per tenant
- **Roles & Permissions**: Enum-based permission system (no database overhead)
- Secure password hashing and management
- First tenant user creation by central admins
- Profile management for all users

### 🔑 Role-Based Access Control

- **Central Roles**:
  - `central_admin`: Full platform access (create/delete tenants, manage all users)
  - `central_user`: Read-only access to platform data
- **Tenant Roles**:
  - `tenant_admin`: Full organization management (create/manage users, settings)
  - `tenant_user`: Basic access (view data, update own profile)
- **Type-safe**: Using PHP 8.3 Enums for compile-time safety
- **Zero overhead**: No database queries for permission checks

### 🛠️ Developer Experience

- **Laravel Actions**: Business logic abstraction with `lorisleiva/laravel-actions`
- **API First**: RESTful API ready for mobile/SPA
- **Bruno Collection**: Complete API testing suite included
- **Logging**: Detailed logs for debugging and monitoring
- **Validation**: Comprehensive request validation
- **Error Handling**: Consistent error responses

## 🏛️ Architecture

### Multi-Tenant Strategy
```
Central Database (pgsql)
├── tenants (id uuid, name, data)
├── users (id uuid, name, email, password, role)
└── personal_access_tokens (Sanctum)

Tenant Database (tenant_xxx)
├── users (id uuid, name, email, password, role)
├── migrations
└── [your business tables]
```

### User Architecture

This starter uses **complete database isolation** where each tenant has its own user table:

**Central Users (Platform Admins):**
- Stored in central database
- Can create and manage tenants (central_admin only)
- Can create the first user in any tenant
- Have access to all tenant contexts
- Roles: `central_admin`, `central_user`

**Tenant Users (Organization Members):**
- Stored in tenant-specific database
- Can only access their own tenant
- Completely isolated from other tenants
- Managed within their tenant context
- Roles: `tenant_admin`, `tenant_user`

**Important:** Users cannot belong to multiple tenants with this architecture. Each user exists in either the central database OR a specific tenant database, ensuring complete data isolation.

### Request Flow
```
API Request
    ↓
Authentication (Sanctum Token)
    ↓
Permission Check (Enum-based)
    ↓
X-Tenant-ID Header (for tenant routes)
    ↓
InitializeTenancyByRequestData Middleware
    ↓
Tenant Context Initialized
    ↓
Database Connection Switched → tenant_xxx
    ↓
Action Executed
    ↓
Response
```

### Actions Pattern
```
app/Actions/
├── Central/
│   ├── Users/         (Platform user management)
│   │   ├── CreateUser
│   │   ├── UpdateUser
│   │   └── DeleteUser
│   └── Tenants/       (Tenant CRUD operations)
│       ├── CreateTenant
│       ├── UpdateTenant
│       ├── DeleteTenant
│       └── CreateFirstTenantUser
└── Tenant/
    └── Users/         (Tenant user management)
        ├── CreateUser
        ├── UpdateUser
        └── DeleteUser
```

## 📦 Prerequisites

- **PHP** >= 8.3
- **Composer** >= 2.7
- **PostgreSQL** >= 16
- **Node.js** >= 20 (optional, for frontend)
- **Bruno** (for API testing)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://gitea.acti-sync.fr/ActiSync/laravel-multi-tenant-api-example.git
cd laravel-multitenant-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
# Central database migrations
php artisan migrate

# The tenant databases will be created automatically when you create tenants
```

### 6. Seed First Central User
```bash
php artisan db:seed --class=InitialCentralUserSeeder
```

This creates:
- Central Admin: `central_admin@example.com` / `Password@123` // Should be defined in .env file

### 7. Start Development Server
```bash
php artisan serve
```

Your API is now running at `http://localhost:8000` 🎉

## ⚙️ Configuration

### Tenancy Configuration

Edit `config/tenancy.php`:
```php
'database' => [
    'central_connection' => 'pgsql',
    'prefix' => 'tenant_',      // Tenant database prefix
    'suffix' => '',
    'managers' => [
        'pgsql' => \Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
    ],
],

'identification' => [
    'resolvers' => [
        Stancl\Tenancy\Resolvers\RequestDataTenantResolver::class => [
            'header' => 'X-Tenant',  // Identification header
        ],
    ],
],
```

### Sanctum Configuration
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## 💡 Usage

### Quick Start Guide

#### 1. Register First Central User (Becomes Central Admin)
This will be created automatically when you run the initial seed.
Refer to the Installation section for more details.

```

#### 2. Create a Tenant
```bash
POST /api/tenants
Authorization: Bearer {your-auth-token}
Content-Type: application/json

{
  "name": "Acme Corporation",
  "admin_name": "John Doe",
  "admin_email": "john@acme.com",
  "admin_password": "password123"
}
```

This will:
1. Create the tenant record with UUID
2. Create a PostgreSQL database `tenant_xxx`
3. Run tenant migrations
4. Create the first admin user with role `tenant_admin`

#### 3. Login to Tenant
```bash
POST /api/tenant/auth/login
X-Tenant: {tenant-uuid}
Content-Type: application/json

{
  "email": "john@acme.com",
  "password": "password123"
}
```

#### 4. Access Tenant Resources
```bash
GET /api/tenant/users
Authorization: Bearer {tenant-token}
X-Tenant: {tenant-uuid}
```

## 🔑 Roles & Permissions

### Central Roles

| Role | Permissions | Description |
|------|------------|-------------|
| **central_admin** | `tenants.*`<br>`central.users.*`<br>`tenant.users.create` | Full platform access. Can create/delete tenants, manage all central users, create first tenant users. |
| **central_user** | `tenants.view`<br>`central.users.view` | Read-only access to platform data. |

### Tenant Roles

| Role | Permissions | Description |
|------|------------|-------------|
| **tenant_admin** | `tenant.users.*`<br>`tenant.settings.*`<br>`profile.*` | Full organization management. Can create/manage users, update settings. |
| **tenant_user** | `tenant.users.view`<br>`profile.*` | Basic access. Can view organization data and manage own profile. |

### Permission Matrix

#### Central Context

| Action | central_admin | central_user |
|--------|------------|-------|
| Create Tenant | ✅ | ❌ |
| Delete Tenant | ✅ | ❌ |
| View Tenants | ✅ | ✅ |
| Create Central User | ✅ | ❌ |
| Delete Central User | ✅ | ❌ |
| Create First Tenant User | ✅ | ❌ |

#### Tenant Context

| Action | tenant_admin | tenant_user |
|--------|--------------|-------------|
| Create User | ✅ | ❌ |
| Update User | ✅ | ❌ |
| Delete User | ✅ | ❌ |
| View Users | ✅ | ✅ |
| Update Settings | ✅ | ❌ |
| Update Own Profile | ✅ | ✅ |

### Security Rules

1. **First Central User** → Automatically `central_admin`
2. **First Tenant User** → Automatically `tenant_admin`
3. **Self-Deletion** → Prevented for all users
4. **Last Central Admin** → Cannot be deleted or demoted
5. **Cross-Tenant Access** → Completely blocked
6. **Tenant to Central** → No tenant user can access central APIs

## 🧪 API Testing

### Using Bruno

This project includes a complete Bruno collection for API testing.

#### Install Bruno
```bash
# macOS
brew install bruno

# Linux/Windows
# Download from https://www.usebruno.com/downloads
```

#### Import Collection

1. Open Bruno
2. Open Collection → `_api_docs`
3. Select Environment → `local`

### Collection Structure
```
./.api_docs/
├── bruno.json
├── central/
│   ├── Login.bru
│   ├── Logout.bru
│   ├── Me.bru
│   ├── Tenants/
│   │   ├── CreateTenant.bru
│   │   ├── DeleteTenant.bru
│   │   ├── ListTenants.bru
│   │   └── UpdateTenant.bru
│   ├── Users/
│   │   ├── CreateUser.bru
│   │   ├── DeleteUser.bru
│   │   ├── GetUser.bru
│   │   ├── ListUsers.bru
│   │   └── UpdateUser.bru
│   └── folder.bru
├── environments/
│   └── local.bru
└── tenant/
    ├── Login.bru
    ├── Logout.bru
    ├── Me.bru
    ├── Users/
    │   ├── CreateUser.bru
    │   ├── DeleteUser.bru
    │   ├── GetUser.bru
    │   ├── ListUsers.bru
    │   └── UpdateUser.bru
    └── folder.bru
```
#### Run Tests
```bash
# Run entire collection
bru run _api_docs --env local

# Run specific folder
bru run _api_docs/central\  --env local
bru run _api_docs/tenant\  --env local
```

### Automated Testing (Coming soon)
```bash
# Make test script executable
chmod +x test-tenant-workflow.sh

# Run complete workflow
./test-tenant-workflow.sh
```

## 📁 Project Structure
```
.
├── app/
│   ├── Actions/              # Business logic (Laravel Actions)
│   │   ├── Central/
│   │   │   ├── Auth/        # Central authentication
│   │   │   ├── Users/       # Platform user management
│   │   │   └── Tenants/     # Tenant management
│   │   └── Tenant/
│   │       ├── Auth/        # Tenant authentication
│   │       └── Users/       # Tenant user management
│   ├── Enums/
│   │   ├── CentralRole.php  # Central roles & permissions
│   │   └── TenantRole.php   # Tenant roles & permissions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── Central/     # Central API controllers
│   │   │   └── Tenant/      # Tenant API controllers
│   │   └── Middleware/
│   │       ├── CheckCentralPermission.php
│   │       └── CheckTenantPermission.php
│   ├── Models/
│   │   ├── Tenant.php       # Tenant model
│   │   └── User.php         # User model (with role methods)
│   └── Providers/
│       └── TenancyServiceProvider.php
├── config/
│   └── tenancy.php          # Tenancy configuration
├── database/
│   ├── migrations/          # Central migrations
│   ├── migrations/tenant/   # Tenant migrations
│   └── seeders/
│       └── CentralUserSeeder.php
├── routes/
│   ├── api.php              # Central API routes
│   └── tenant.php           # Tenant API routes
├── bruno-api-tests/         # Bruno API test collection
├── tests/                   # PHPUnit tests
├── CHANGELOG.md
├── CONTRIBUTING.md
└── README.md
```

## 📖 API Documentation

### Central Endpoints

#### Authentication
```http
POST   /api/auth/login         Login and get token
POST   /api/auth/logout        Logout and revoke token
GET    /api/auth/me            Get current user info
```

#### Tenant Management (Central Admin Only)
```http
GET    /api/tenants            List all tenants
POST   /api/tenants            Create new tenant + first admin
GET    /api/tenants/{id}       Get tenant details
PUT    /api/tenants/{id}       Update tenant
DELETE /api/tenants/{id}       Delete tenant + database
POST   /api/tenants/{id}/users Create first user in tenant
```

#### User Management (Central Admin Only)
```http
GET    /api/users              List platform users
POST   /api/users              Create platform user
GET    /api/users/{id}         Get user details + permissions
PUT    /api/users/{id}         Update user
DELETE /api/users/{id}         Delete user (cannot delete self/last central admin)
```

### Tenant Endpoints

**All tenant endpoints require the `X-Tenant` header.**

#### Authentication
```http
POST   /api/tenant/auth/login     Login to tenant
POST   /api/tenant/auth/logout    Logout from tenant
GET    /api/tenant/auth/me        Get current tenant user
```

#### Tenant Info
```http
GET    /api/tenant/info           Get tenant info + user role & permissions
```

#### User Management
```http
GET    /api/tenant/users          List tenant users (all roles)
POST   /api/tenant/users          Create user (tenant_admin only)
GET    /api/tenant/users/{id}     Get user details (all roles)
PUT    /api/tenant/users/{id}     Update user (tenant_admin only)
DELETE /api/tenant/users/{id}     Delete user (tenant_admin only)
```

#### Profile Management
```http
GET    /api/tenant/profile        Get own profile
PUT    /api/tenant/profile        Update own profile (all roles)
```

### Request/Response Examples

#### Create Tenant with First Admin

**Request:**
```bash
POST /api/tenants
Authorization: Bearer {central_admin_token}
Content-Type: application/json

{
  "name": "Acme Corporation",
  "admin_name": "John Doe",
  "admin_email": "john@acme.com",
  "admin_password": "password123"
}
```

**Response:**
```json
{
  "message": "Tenant created successfully",
  "tenant": {
    "id": "9d8a7f3b-4c2e-4a1d-b8e7-3f2c1a9d8e7f",
    "name": "Acme Corporation",
    "created_at": "2025-11-24T..."
  }
}
```

#### Get Tenant Info with Permissions

**Request:**
```bash
GET /api/tenant/info
Authorization: Bearer {tenant_token}
X-Tenant: 9d8a7f3b-4c2e-4a1d-b8e7-3f2c1a9d8e7f
```

**Response:**
```json
{
  "tenant_id": "9d8a7f3b-4c2e-4a1d-b8e7-3f2c1a9d8e7f",
  "tenant_name": "Acme Corporation",
  "database": "tenant_9d8a7f3b4c2e4a1db8e73f2c1a9d8e7f",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@acme.com",
    "role": "tenant_admin",
    "permissions": [
      "tenant.users.create",
      "tenant.users.view",
      "tenant.users.update",
      "tenant.users.delete",
      "tenant.settings.view",
      "tenant.settings.update",
      "profile.view",
      "profile.update"
    ]
  }
}
```

#### Permission Denied Response

**Request:**
```bash
POST /api/tenant/users
Authorization: Bearer {member_token}
X-Tenant: xxx
```

**Response:**
```json
{
  "message": "Forbidden. You do not have permission to perform this action.",
  "required_permission": "tenant.users.create",
  "your_role": "member",
  "your_permissions": [
    "tenant.users.view",
    "profile.view",
    "profile.update"
  ],
  "tenant_id": "xxx"
}
```

## 🔒 Security

### Authentication

- **Sanctum tokens** for stateless API authentication
- **Password hashing** with laravel password hashing
- **Token revocation** on logout

### Multi-Tenant Security

- **Complete database isolation**: Each tenant has separate database
- **Middleware validation**: Tenant ID validated on every request
- **Role-based access control**: Enum-based permissions (zero overhead)
- **No cross-tenant access**: Users cannot access other tenant data
- **SQL injection protection**: Prepared statements and Eloquent ORM

### Permission System

- **Type-safe**: PHP 8.3 Enums with compile-time checking
- **Zero overhead**: No database queries for permission checks
- **Immutable**: Permissions defined in code (version controlled)
- **Explicit**: Clear permission names (`tenant.users.create`)

### Best Practices

1. **Always use HTTPS** in production
2. **Set strong APP_KEY** in `.env`
3. **Configure CORS** properly in `config/cors.php`
4. **Enable rate limiting** for API endpoints
5. **Regular security audits** with `composer audit`
6. **Keep dependencies updated**
7. **Review logs** regularly for suspicious activity

## 🚀 Performance

### Database Optimization
```php
// Use indexes in migrations
$table->string('email')->index();
$table->uuid('tenant_id')->index();
$table->string('role')->index();
```

### Caching
```php
// Cache tenant queries
$tenant = Cache::remember("tenant.{$id}", 3600, function () use ($id) {
    return Tenant::find($id);
});
```

### Permission Checks
```php
// ✅ Zero DB queries - uses Enum
if ($user->canTenant('tenant.users.create')) {
    // ...
}
```

### Queue Processing
```php
// Dispatch actions as jobs for long-running tasks
CreateTenant::dispatch($data);
```

### Connection Pooling

Use **PgBouncer** for PostgreSQL connection pooling in production with hundreds of tenants.

## 🧩 Extending the Starter

### Add Custom Tenant Tables

1. Create migration in `database/migrations/tenant/`:
```bash
php artisan make:migration create_products_table --path=database/migrations/tenant
```

2. Run migrations for all tenants:
```bash
php artisan tenants:migrate
```

### Add Custom Actions
```php
// app/Actions/Tenant/Products/CreateProduct.php
<?php

namespace App\Actions\Tenant\Products;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCustomer
{
    use AsAction;

    public function handle(array $data): Customer
    {
        return Customer::create($data);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
```

### Add Custom Permissions
```php
// app/Enums/TenantRole.php

public function permissions(): array
{
    return match($this) {
        self::TENANT_ADMIN => [
            // Existing permissions...
            
            // Add new ones
            'customers.create',
            'customers.update',
            'customers.delete',
        ],
        // ...
    };
}
```

### Add Custom Routes
```php
// routes/tenant.php
Route::middleware(['auth:sanctum', 'permission.tenant:customers.create'])->group(function () {
    Route::post('/customers', [CustomerController::class, 'store']);
});
```

## 🛠️ Useful Commands
```bash
# Tenant Management
php artisan tenants:list                      # List all tenants
php artisan tenants:migrate                   # Migrate all tenants
php artisan tenants:run migrate --tenants=xxx # Migrate specific tenant
php artisan tenants:rollback                  # Rollback tenant migrations

# Cache Management
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan event:clear

# View Routes
php artisan route:list
php artisan route:list --path=api
php artisan route:list --path=tenant

# Logs
tail -f storage/logs/laravel.log            # Monitor logs
echo "" > storage/logs/laravel.log          # Clear logs

# Database
php artisan db:show                         # Show database info
php artisan migrate:status                  # Check migration status

# Testing
php artisan test                            # Run PHPUnit tests
bru run bruno-api-tests --env local         # Run Bruno tests
```

## 🐛 Troubleshooting

### Tenant database not created

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Verify events:**
```bash
php artisan event:list
```

**Manually trigger:** (coming soon)
```bash
php artisan tenants:migrate
```

### 401 Unauthorized

- Verify token is valid and not expired
- Check `Authorization: Bearer {token}` header format
- Ensure user exists in correct context (central vs tenant)
- Clear token cache: `php artisan cache:clear`

### 403 Forbidden (Permission Denied)

- Check user role: `GET /api/auth/me`
- Verify required permission in error response
- Review role permissions in `app/Enums/CentralRole.php` or `TenantRole.php`

### 404 Tenant not found

- Verify `X-Tenant` header is set and correct
- Check tenant exists: `php artisan tenants:list` // Coming soon
- Verify tenant database exists in PostgreSQL:
```sql
  SELECT id FROM tenants WHERE id = {tenant_id};
```

### Database connection issues

- Check PostgreSQL is running
- Test connection: `php artisan db:show`
- Verify credentials in `.env`
- Check PostgreSQL logs for connection errors

### Permission check not working

- Clear config cache: `php artisan config:clear`
- Verify middleware is registered in `bootstrap/app.php`
- Check user has the correct role in database
- Review enum permissions match your needs

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on:

- Code of Conduct
- Development workflow
- Coding standards (PSR-12)
- Commit message guidelines
- Pull request process

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes and version history.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Stancl Tenancy](https://tenancyforlaravel.com) - Multi-tenancy package
- [Laravel Actions](https://laravelactions.com) - Action pattern implementation
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API authentication
- [Bruno](https://www.usebruno.com) - Open-source API testing tool

## 📧 Support

- 📖 [Documentation](https://gitea.acti-sync.fr/ActiSync/laravel-multi-tenant-api-example.git/wiki)
- 🐛 [Issue Tracker](https://gitea.acti-sync.fr/ActiSync/laravel-multi-tenant-api-example.git/issues)

## Todos ##
- [ ] Add soft deletation for tenants database and resources
- [ ] Make Tenant identification strategy configurable
- [ ] Test and add more supported databases
- [ ] Test and add more supported authentication methods
- [ ] Add Pest testing
---


<p align="center">
  <a href="#-table-of-contents">Back to top ⬆️</a>
</p>