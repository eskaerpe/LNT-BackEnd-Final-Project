# Electronic Catalog PIM - Backend API

**Production-Ready RESTful API for Electronic Store Catalog Management**

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![JWT](https://img.shields.io/badge/Auth-JWT-green)
![Status](https://img.shields.io/badge/Status-Complete-brightgreen)

## 📋 Project Overview

A comprehensive RESTful API backend for an Electronic Store content management system, separating public read-only access for the storefront from a secure authenticated admin dashboard for full catalog management.

### Key Features

✅ **JWT Authentication** - Secure admin endpoints with JSON Web Tokens  
✅ **Complete CRUD Operations** - Categories, Products, and Users management  
✅ **Public Storefront API** - Read-only endpoints with pagination and filtering  
✅ **File Upload Support** - Product image uploads with validation (jpg/jpeg/png, max 2MB)  
✅ **Database Integrity** - ON DELETE RESTRICT constraints for data safety  
✅ **Comprehensive Error Handling** - Consistent JSON error responses  
✅ **Automated Testing** - 54-test suite with 100% pass rate  
✅ **Production Ready** - All PRD requirements implemented and validated

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.3+
- Composer
- MySQL 8.0+ or SQLite
- Node.js (optional, for frontend)

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd final-project-lnt-be

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env

# 4. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=electronic_catalog
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Generate application key
php artisan key:generate

# 6. Generate JWT secret
php artisan jwt:secret

# 7. Run migrations and seeders
php artisan migrate --seed

# 8. Create storage symlink
php artisan storage:link

# 9. Start the development server
php artisan serve --port=8000
```

**API will be available at:** `http://127.0.0.1:8000/api`

---

## 📚 API Documentation

### Complete API Reference

See [API_DOCS.md](API_DOCS.md) for detailed endpoint documentation including:

- Authentication (Login, Logout, Get Current User)
- Public Endpoints (Categories, Products with filtering)
- Admin CRUD Operations (Categories, Products, Users)
- Error Handling and Response Formats
- Example Requests

### Quick API Examples

**Login:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Get Products with Filters:**

```bash
curl "http://127.0.0.1:8000/api/products?page=1&per_page=10&category=laptops&search=gaming"
```

**Create Product (JSON):**

```bash
TOKEN="your_jwt_token"
curl -X POST http://127.0.0.1:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "name": "Gaming Mouse",
    "slug": "gaming-mouse",
    "description": "Precision gaming mouse",
    "price": 79.99,
    "stock": 50,
    "image_url": "https://via.placeholder.com/300"
  }'
```

---

## 🔧 Project Structure

```
final-project-lnt-be/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API controllers (Auth, Categories, Products, Users, Public)
│   │   ├── Middleware/           # Middleware (auth:api)
│   │   └── Requests/             # Form request validation classes
│   ├── Models/                   # Eloquent models (User, Category, Product)
│   └── Providers/                # Service providers
├── bootstrap/
│   └── app.php                   # Application bootstrap with exception handlers
├── config/
│   ├── auth.php                  # JWT guard configuration
│   ├── database.php              # Database configuration
│   └── ...
├── database/
│   ├── migrations/               # Database migrations
│   ├── factories/                # Model factories
│   └── seeders/                  # Database seeders
├── routes/
│   └── api.php                   # API routes (public & admin)
├── storage/
│   ├── app/public/products/      # Uploaded product images
│   └── logs/                     # Application logs
├── tests/                        # Test cases
├── API_DOCS.md                   # Complete API documentation
├── PLAN.md                       # Project phases and planning
├── TASKS.md                      # Task checklist
├── CHANGELOG.md                  # Version history and changes
├── PRD.md                        # Product requirements document
├── SESSION_STATE.md              # Phase completion reports
├── test_api_suite.php            # Automated test harness (54 tests)
├── test_file_upload.php          # File upload test script
└── Electronic-Catalog-API.postman_collection.json  # Postman collection
```

---

## 🧪 Testing

### Run Full Test Suite

```bash
# 54 automated tests covering all endpoints
php test_api_suite.php
```

**Expected Output:** `Tests Passed: 54 / 54 (100%)`

### Test File Uploads

```bash
# Test both file upload and URL-based image handling
php test_file_upload.php
```

---

## 📦 API Endpoints Summary

### Authentication

- `POST /auth/login` - Login with email/password, returns JWT token
- `POST /auth/logout` - Logout and invalidate token
- `GET /auth/me` - Get current authenticated user

### Public Endpoints (No Auth Required)

- `GET /categories` - List all categories (paginated)
- `GET /categories/{id}` - Get category with products
- `GET /products` - List products (paginated, filterable)
- `GET /products/{slug}` - Get product by slug

### Admin Endpoints (JWT Required)

**Categories:**

- `GET /admin/categories` - List categories
- `POST /admin/categories` - Create category
- `PUT /admin/categories/{id}` - Update category
- `DELETE /admin/categories/{id}` - Delete category (409 if products exist)

**Products:**

- `GET /admin/products` - List products
- `POST /admin/products` - Create product (with file upload or URL)
- `PUT /admin/products/{id}` - Update product
- `DELETE /admin/products/{id}` - Delete product

**Users:**

- `GET /admin/users` - List users
- `POST /admin/users` - Create user
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user (prevents self-deletion and last-admin deletion)

---

## 🗄️ Database Schema

### Tables

**users**

- id, name, email (unique), password, timestamps

**categories**

- id, name (unique), slug (unique), description, timestamps

**products**

- id, category_id (FK, ON DELETE RESTRICT), name, slug (unique), description, price (decimal), stock (int), image_url (nullable), timestamps

### Relationships

- One Category has Many Products
- One User can manage Many Categories/Products

---

## 🔐 Authentication

JWT Bearer Token authentication for admin endpoints.

**Header Format:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Default Seeded Admin:**

- Email: `admin@example.com`
- Password: `password`

---

## 📤 File Uploads

Product images can be uploaded as files or provided as URLs.

**File Upload Validation:**

- Supported formats: JPG, JPEG, PNG
- Max file size: 2MB
- Storage location: `storage/app/public/products/`
- Web accessible at: `/storage/products/{filename}`

**Example File Upload (PHP):**

```bash
php test_file_upload.php
```

---

## 🚀 Deployment

### Environment Configuration

```bash
# Production environment
APP_ENV=production
APP_DEBUG=false
JWT_ALGORITHM=HS256
DB_CONNECTION=mysql
DB_HOST=your_database_host
DB_PORT=3306
DB_DATABASE=electronic_catalog
DB_USERNAME=db_user
DB_PASSWORD=secure_password
```

### Production Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure proper database credentials
- [ ] Set strong `APP_KEY` and `JWT_SECRET`
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS headers if needed
- [ ] Set up proper logging
- [ ] Configure file storage to use cloud storage (S3, etc.)
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`

---

## 📝 Import Postman Collection

1. Open Postman
2. Click **Import** → **Upload Files**
3. Select `Electronic-Catalog-API.postman_collection.json`
4. Set environment variables:
    - `base_url`: `http://127.0.0.1:8000/api`
    - `token`: (auto-populated after login)

---

## 📋 Phases Completed

### Phase 1: Project Initialization ✅

- Laravel 13 setup with PHP 8.3
- JWT authentication configured
- Environment and database setup

### Phase 2: Database Architecture ✅

- Users, Categories, Products tables with proper relationships
- ON DELETE RESTRICT constraints for data integrity
- Database seeders with 20+ sample products

### Phase 3: Admin API Development ✅

- JWT authentication (login/logout/me)
- Complete CRUD for Categories, Products, Users
- Business logic: prevent self-deletion, last-admin protection
- Comprehensive error handling and validation

### Phase 4: Public API Development ✅

- Read-only category and product endpoints
- Pagination with configurable per-page limits
- Filtering by category slug and product search
- File upload support with validation
- Storage symlink for image access

### Phase 5: Testing & Documentation ✅

- 54-test automated test suite (100% pass rate)
- Comprehensive API documentation (API_DOCS.md)
- Postman collection for manual testing
- File upload test scripts

---

## 🔍 Key Implementation Details

### Exception Handling

All errors return consistent JSON responses through centralized exception handler in `bootstrap/app.php`:

- 401 Unauthorized (invalid/missing token)
- 404 Not Found (missing resources)
- 409 Conflict (ON DELETE RESTRICT violations)
- 422 Unprocessable Entity (validation failures)
- 500 Internal Server Error

### Validation

Form Request classes enforce validation rules:

- Email uniqueness and format
- Price and stock minimum values
- Slug format (lowercase, hyphens, numbers only)
- File type and size restrictions

### Data Integrity

- Foreign key constraints with ON DELETE RESTRICT
- Self-deletion prevention in user management
- Last-admin protection (prevents deleting only admin user)
- Cascading soft-delete support

---

## 🛠️ Development Commands

```bash
# Database operations
php artisan migrate                # Run migrations
php artisan migrate:fresh --seed   # Reset database with seeds
php artisan migrate:rollback       # Rollback migrations

# Cache management
php artisan config:cache           # Cache configuration
php artisan route:cache            # Cache routes
php artisan optimize:clear         # Clear all caches

# Code generation
php artisan tinker                 # Interactive shell

# Testing
php test_api_suite.php             # Run API tests
php test_file_upload.php           # Test file uploads
```

---

## 📞 Support & Troubleshooting

### Common Issues

**CORS Errors:**

- Configure CORS headers in `bootstrap/app.php`
- Add appropriate `withMiddleware()` middleware

**Storage Link Issues:**

```bash
php artisan storage:link  # Recreate symlink
```

**JWT Secret Missing:**

```bash
php artisan jwt:secret  # Generate JWT secret
```

**Database Connection Error:**

- Verify `.env` database configuration
- Check database server is running
- Run: `php artisan migrate --seed`

---

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 👨‍💻 Contributors

**LNT Final Project Backend** - Electronic Catalog API  
Built with Laravel 13, PHP 8.3, MySQL 8.0+

---

## 📅 Project Status

- **Overall Status:** ✅ **PRODUCTION READY**
- **All Phases:** Complete
- **Test Coverage:** 54/54 tests passing (100%)
- **PRD Compliance:** 100% - All requirements met
- **Last Updated:** May 3, 2026
