# AKSA Employee Management System - Backend

Link Github : https://github.com/travian22/aksa-2
Link Deployment : https://aksa-be.chilltrav.tech

## Tentang Project

**AKSA Employee Management System** adalah aplikasi backend untuk manajemen karyawan yang komprehensif. Sistem ini memungkinkan organisasi untuk mengelola data karyawan, divisi, kehadiran, dan aktivitas audit dengan mudah dan efisien.

### Fitur Utama

- ✅ **Manajemen Karyawan** - CRUD lengkap untuk data karyawan
- ✅ **Manajemen Divisi** - Mengelola struktur organisasi
- ✅ **Tracking Kehadiran** - Pencatatan dan analisis kehadiran
- ✅ **Activity Logs** - Audit trail untuk semua perubahan sistem
- ✅ **Dashboard Analytics** - Statistik dan ringkasan data
- ✅ **User Management** - Manajemen pengguna dengan hak akses
- ✅ **Export Data** - Export data karyawan ke berbagai format
- ✅ **Token-based Authentication** - Keamanan dengan Laravel Sanctum

## Teknologi Stack

| Layer | Teknologi |
|-------|-----------|
| **Framework** | Laravel 11 |
| **Language** | PHP 8.2+ |
| **Database** | MySQL |
| **Authentication** | Laravel Sanctum |
| **API Format** | RESTful JSON |
| **Frontend Tools** | Vite, Tailwind CSS, Vue.js (optional) |
| **Testing** | Pest PHP |
| **Container** | Docker, Docker Compose |

## Struktur Project

```
aksa-be/
├── app/
│   ├── Http/Controllers/     # Controller API endpoints
│   ├── Models/              # Database models (User, Employee, Division, etc)
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database schema migrations
│   ├── factories/           # Model factories untuk testing
│   └── seeders/             # Database seeders
├── routes/
│   └── api.php              # API routes
├── tests/                   # Unit & Feature tests (Pest)
├── docker/                  # Docker configuration
└── config/                  # Konfigurasi aplikasi
```

## API Endpoints

Total **29 endpoints** dengan dokumentasi lengkap di [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

### Kategori Endpoint:

1. **Authentication** (2 endpoints)
   - POST `/login` - Login user
   - POST `/logout` - Logout user

2. **Profile** (3 endpoints)
   - GET `/profile` - Lihat profil user
   - PUT `/profile` - Update profil user
   - PUT `/profile/password` - Ubah password

3. **User Management** (3 endpoints)
   - POST `/register` - Daftar user baru
   - GET `/users` - List semua users
   - DELETE `/users/{id}` - Hapus user

4. **Divisions** (5 endpoints)
   - CRUD lengkap untuk divisi/departemen

5. **Employees** (8 endpoints)
   - CRUD lengkap, export, summary, bulk delete

6. **Attendances** (4 endpoints)
   - Kelola data kehadiran dengan summary

7. **Activity Logs** (2 endpoints)
   - Lihat log aktivitas sistem

8. **Dashboard** (1 endpoint)
   - GET `/dashboard` - Ringkasan data dashboard

## Instalasi & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 5.7+
- Node.js 16+
- Docker (optional)

### Setup Local

```bash
# Clone repository
git clone <repository-url>
cd aksa-be

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Run development server
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

### Setup dengan Docker

```bash
# Build dan jalankan container
docker-compose up --build

# Akses container PHP
docker-compose exec app bash

# Jalankan migrasi
php artisan migrate --seed

# Akses di http://localhost
```

## Development Commands

```bash
# Development server dengan hot reload
composer run dev

# Database migration
php artisan migrate              # Jalankan semua migrations
php artisan migrate:fresh --seed # Reset database + seed

# Tinker shell (PHP interactive)
php artisan tinker

# Run tests
composer test

# Code quality checks
composer pint    # Format code
composer stan    # Static analysis
```

## Authentication

Semua endpoint kecuali `/login` memerlukan **Bearer Token** di header:

```
Authorization: Bearer {token}
```

Dapatkan token dengan login:

```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

## Response Format

### Success Response
```json
{
  "status": "success",
  "message": "Data berhasil diambil",
  "data": { ... }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Pesan error"
}
```

Lihat [API_DOCUMENTATION.md](API_DOCUMENTATION.md) untuk format detail lengkap.

## Database Models

- **User** - Data pengguna sistem
- **Employee** - Data karyawan
- **Division** - Struktur divisi/departemen
- **Attendance** - Data kehadiran
- **ActivityLog** - Audit trail

## Testing

```bash
# Jalankan semua tests
composer test

# Test specific file
composer test tests/Feature/ExampleTest.php

# Test dengan coverage
composer test -- --coverage
```

Menggunakan framework testing **Pest PHP** yang modern dan mudah digunakan.

## Contributing

Kontribusi sangat diterima! Silakan:

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
