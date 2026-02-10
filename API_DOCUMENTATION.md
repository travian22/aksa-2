# AKSA Employee Management System - API Documentation

## Overview

- **Base URL**: `http://localhost:8000/api`
- **Framework**: Laravel 11 + Sanctum (Token-based auth)
- **All IDs**: UUID format (e.g. `"9e1a2b3c-4d5e-6f7a-8b9c-0d1e2f3a4b5c"`)
- **Authentication**: Bearer Token via header `Authorization: Bearer {token}`
- **Total Endpoints**: 29

## Authentication

Semua endpoint **kecuali** `POST /api/login` membutuhkan header:
```
Authorization: Bearer {token}
```

Jika token tidak valid atau tidak ada, API akan merespons:
```json
// HTTP 401
{
  "message": "Unauthenticated."
}
```

## Response Format

Semua response mengikuti format konsisten:

### Success Response
```json
{
  "status": "success",
  "message": "Pesan deskriptif",
  "data": { ... }
}
```

### Success Response dengan Pagination
```json
{
  "status": "success",
  "message": "Pesan deskriptif",
  "data": { ... },
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50,
    "from": 1,
    "to": 10,
    "next_page_url": "http://localhost:8000/api/xxx?page=2",
    "prev_page_url": null
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Pesan error deskriptif"
}
```

### Validation Error Response (HTTP 422)
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."],
    "phone": ["The phone field is required."]
  }
}
```

## HTTP Status Codes

| Code | Keterangan |
|------|-----------|
| 200  | OK - Request berhasil |
| 201  | Created - Data berhasil dibuat |
| 401  | Unauthorized - Token tidak valid |
| 404  | Not Found - Data tidak ditemukan |
| 422  | Unprocessable Entity - Validasi gagal |

---

## 1. AUTH

### 1.1 Login

```
POST /api/login
Content-Type: application/json
```

**Request Body:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Validation Rules:**
| Field    | Rules           |
|----------|-----------------|
| username | required, string |
| password | required, string |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "token": "1|abc123xyz...",
    "admin": {
      "id": "9e1a2b3c-4d5e-6f7a-8b9c-0d1e2f3a4b5c",
      "name": "Admin",
      "username": "admin",
      "phone": "081234567890",
      "email": "admin@example.com"
    }
  }
}
```

**Error Response (401):**
```json
{
  "status": "error",
  "message": "Username atau password salah",
  "data": null
}
```

### 1.2 Register Admin

```
POST /api/register
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Admin Baru",
  "username": "adminbaru",
  "email": "adminbaru@example.com",
  "phone": "081234567890",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Validation Rules:**
| Field                 | Rules                              |
|-----------------------|------------------------------------|
| name                  | required, string, max:255          |
| username              | required, string, max:255, unique  |
| email                 | required, email, max:255, unique   |
| phone                 | required, string, max:20           |
| password              | required, string, min:8, confirmed |
| password_confirmation | (harus sama dengan password)       |

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Admin baru berhasil ditambahkan",
  "data": {
    "admin": {
      "id": "uuid",
      "name": "Admin Baru",
      "username": "adminbaru",
      "phone": "081234567890",
      "email": "adminbaru@example.com"
    }
  }
}
```

### 1.3 Logout

```
POST /api/logout
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Logout berhasil"
}
```

---

## 2. DASHBOARD

### 2.1 Get Dashboard Statistics

```
GET /api/dashboard
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data dashboard berhasil diambil",
  "data": {
    "total_employees": 25,
    "total_divisions": 6,
    "employees_per_division": [
      {
        "id": "uuid",
        "name": "Backend",
        "total_employees": 8
      },
      {
        "id": "uuid",
        "name": "Frontend",
        "total_employees": 5
      }
    ],
    "recent_employees": [
      {
        "id": "uuid",
        "name": "John Doe",
        "position": "Senior Developer",
        "division": "Backend",
        "image": "/storage/employees/xxx.jpg",
        "created_at": "2026-02-10T12:00:00.000000Z"
      }
    ]
  }
}
```

---

## 3. PROFILE

### 3.1 Get Profile

```
GET /api/profile
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data profil berhasil diambil",
  "data": {
    "admin": {
      "id": "uuid",
      "name": "Admin",
      "username": "admin",
      "phone": "081234567890",
      "email": "admin@example.com",
      "created_at": "2026-02-09T15:00:00.000000Z",
      "updated_at": "2026-02-09T15:00:00.000000Z"
    }
  }
}
```

### 3.2 Update Profile

```
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Admin Updated",
  "username": "adminupdated",
  "phone": "089876543210",
  "email": "adminupdated@example.com"
}
```

**Validation Rules:**
| Field    | Rules                                       |
|----------|---------------------------------------------|
| name     | required, string, max:255                   |
| username | required, string, max:255, unique (kecuali diri sendiri) |
| phone    | required, string, max:20                    |
| email    | required, email, max:255, unique (kecuali diri sendiri)  |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Profil berhasil diubah",
  "data": {
    "admin": {
      "id": "uuid",
      "name": "Admin Updated",
      "username": "adminupdated",
      "phone": "089876543210",
      "email": "adminupdated@example.com"
    }
  }
}
```

### 3.3 Change Password

```
PUT /api/profile/password
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Validation Rules:**
| Field                     | Rules                    |
|---------------------------|--------------------------|
| current_password          | required, string         |
| new_password              | required, string, min:8, confirmed |
| new_password_confirmation | (harus sama dengan new_password)   |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Password berhasil diubah"
}
```

**Error Response (422) - Password salah:**
```json
{
  "status": "error",
  "message": "Password saat ini salah"
}
```

---

## 4. USER / ADMIN MANAGEMENT

### 4.1 List All Admins

```
GET /api/users
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param | Type   | Keterangan              |
|-------|--------|-------------------------|
| name  | string | Filter by nama (partial match) |
| page  | int    | Nomor halaman           |

**Contoh:** `GET /api/users?name=admin&page=1`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data admin berhasil diambil",
  "data": {
    "users": [
      {
        "id": "uuid",
        "name": "Admin",
        "username": "admin",
        "phone": "081234567890",
        "email": "admin@example.com",
        "created_at": "2026-02-09T15:00:00.000000Z"
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1,
    "from": 1,
    "to": 1,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

### 4.2 Delete Admin

```
DELETE /api/users/{id}
Authorization: Bearer {token}
```

**Path Parameters:**
| Param | Type | Keterangan |
|-------|------|------------|
| id    | uuid | ID admin   |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data admin berhasil dihapus"
}
```

**Error Response (422) - Hapus diri sendiri:**
```json
{
  "status": "error",
  "message": "Tidak bisa menghapus akun sendiri"
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Data admin tidak ditemukan"
}
```

---

## 5. DIVISIONS

### 5.1 List All Divisions

```
GET /api/divisions
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param | Type   | Keterangan                     |
|-------|--------|--------------------------------|
| name  | string | Filter by nama (partial match) |
| page  | int    | Nomor halaman                  |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data divisi berhasil diambil",
  "data": {
    "divisions": [
      {
        "id": "uuid",
        "name": "Backend",
        "created_at": "2026-02-09T16:00:00.000000Z",
        "updated_at": "2026-02-09T16:00:00.000000Z"
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 6,
    "from": 1,
    "to": 6,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

### 5.2 Get Division Detail

```
GET /api/divisions/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data divisi berhasil diambil",
  "data": {
    "division": {
      "id": "uuid",
      "name": "Backend",
      "created_at": "2026-02-09T16:00:00.000000Z",
      "updated_at": "2026-02-09T16:00:00.000000Z",
      "employees_count": 8
    }
  }
}
```

### 5.3 Create Division

```
POST /api/divisions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "DevOps"
}
```

**Validation Rules:**
| Field | Rules                                |
|-------|--------------------------------------|
| name  | required, string, max:255, unique    |

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Data divisi berhasil ditambahkan",
  "data": {
    "division": {
      "id": "uuid",
      "name": "DevOps",
      "created_at": "2026-02-10T12:00:00.000000Z",
      "updated_at": "2026-02-10T12:00:00.000000Z"
    }
  }
}
```

### 5.4 Update Division

```
PUT /api/divisions/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "DevOps Engineering"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data divisi berhasil diubah",
  "data": {
    "division": {
      "id": "uuid",
      "name": "DevOps Engineering",
      "created_at": "2026-02-10T12:00:00.000000Z",
      "updated_at": "2026-02-10T12:05:00.000000Z"
    }
  }
}
```

### 5.5 Delete Division

```
DELETE /api/divisions/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data divisi berhasil dihapus"
}
```

**Error Response (422) - Divisi masih punya karyawan:**
```json
{
  "status": "error",
  "message": "Divisi tidak bisa dihapus karena masih memiliki 5 karyawan"
}
```

---

## 6. EMPLOYEES

### 6.1 List All Employees

```
GET /api/employees
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param       | Type   | Keterangan                     |
|-------------|--------|--------------------------------|
| name        | string | Filter by nama (partial match) |
| division_id | uuid   | Filter by divisi               |
| page        | int    | Nomor halaman                  |

**Contoh:** `GET /api/employees?name=john&division_id=uuid&page=1`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data karyawan berhasil diambil",
  "data": {
    "employees": [
      {
        "id": "uuid",
        "image": "/storage/employees/xxx.jpg",
        "name": "John Doe",
        "phone": "081234567890",
        "division": {
          "id": "uuid",
          "name": "Backend"
        },
        "position": "Senior Developer"
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25,
    "from": 1,
    "to": 10,
    "next_page_url": "http://localhost:8000/api/employees?page=2",
    "prev_page_url": null
  }
}
```

### 6.2 Get Employee Detail

```
GET /api/employees/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data karyawan berhasil diambil",
  "data": {
    "employee": {
      "id": "uuid",
      "image": "/storage/employees/xxx.jpg",
      "name": "John Doe",
      "phone": "081234567890",
      "division": {
        "id": "uuid",
        "name": "Backend"
      },
      "position": "Senior Developer",
      "created_at": "2026-02-09T17:00:00.000000Z",
      "updated_at": "2026-02-09T17:00:00.000000Z"
    }
  }
}
```

### 6.3 Create Employee

```
POST /api/employees
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**PENTING:** Gunakan `multipart/form-data` karena ada file upload (image).

**Request Body (form-data):**
| Field    | Type   | Rules                                         |
|----------|--------|-----------------------------------------------|
| image    | file   | nullable, image, mimes:jpeg,png,jpg,gif, max:2048 KB |
| name     | string | required, string, max:255                     |
| phone    | string | required, string, max:20                      |
| division | uuid   | required, uuid, exists di tabel divisions     |
| position | string | required, string, max:255                     |

> **Catatan**: Field untuk divisi bernama `division` (bukan `division_id`), berisi UUID dari divisi.

**Contoh fetch dari Next.js:**
```javascript
const formData = new FormData();
formData.append('name', 'John Doe');
formData.append('phone', '081234567890');
formData.append('division', 'uuid-divisi');
formData.append('position', 'Senior Developer');
formData.append('image', fileInput.files[0]); // opsional

const res = await fetch('http://localhost:8000/api/employees', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    // JANGAN set Content-Type manual, biarkan browser set boundary
  },
  body: formData,
});
```

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Data karyawan berhasil ditambahkan"
}
```

### 6.4 Update Employee

```
POST /api/employees/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

> **PENTING:** Method tetap `POST` (bukan PUT) karena `multipart/form-data` tidak support PUT di browser. Gunakan `POST`.

**Request Body (form-data):** Sama seperti create (section 6.3).

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data karyawan berhasil diubah"
}
```

### 6.5 Delete Employee

```
DELETE /api/employees/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data karyawan berhasil dihapus"
}
```

### 6.6 Bulk Delete Employees

```
POST /api/employees/bulk-delete
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "ids": [
    "uuid-1",
    "uuid-2",
    "uuid-3"
  ]
}
```

**Validation Rules:**
| Field  | Rules                              |
|--------|------------------------------------|
| ids    | required, array, min:1             |
| ids.*  | required, uuid, exists di employees |

**Success Response (200):**
```json
{
  "status": "success",
  "message": "3 karyawan berhasil dihapus"
}
```

### 6.7 Employee Summary / Statistics

```
GET /api/employees/summary
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Ringkasan karyawan berhasil diambil",
  "data": {
    "total_employees": 25,
    "by_position": [
      {
        "position": "Senior Developer",
        "total": 8
      },
      {
        "position": "Junior Developer",
        "total": 5
      }
    ],
    "by_division": [
      {
        "division": {
          "id": "uuid",
          "name": "Backend"
        },
        "total": 10
      },
      {
        "division": {
          "id": "uuid",
          "name": "Frontend"
        },
        "total": 7
      }
    ]
  }
}
```

### 6.8 Export Employees to CSV

```
GET /api/employees/export
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param       | Type   | Keterangan                     |
|-------------|--------|--------------------------------|
| name        | string | Filter by nama (partial match) |
| division_id | uuid   | Filter by divisi               |

**Response:** File CSV akan di-download langsung (bukan JSON).

- **Content-Type:** `text/csv`
- **Content-Disposition:** `attachment; filename="employees_2026-02-10_120000.csv"`
- **Kolom CSV:** `Name, Phone, Position, Division, Created At`

**Contoh fetch dari Next.js:**
```javascript
const res = await fetch('http://localhost:8000/api/employees/export', {
  headers: { 'Authorization': `Bearer ${token}` },
});
const blob = await res.blob();
const url = window.URL.createObjectURL(blob);
const a = document.createElement('a');
a.href = url;
a.download = 'employees.csv';
a.click();
```

---

## 7. ATTENDANCES (ABSENSI)

### 7.1 List All Attendances

```
GET /api/attendances
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param       | Type   | Keterangan                                    |
|-------------|--------|-----------------------------------------------|
| employee_id | uuid   | Filter by karyawan                            |
| status      | string | Filter by status: `hadir`, `izin`, `sakit`, `alpha` |
| date        | date   | Filter tanggal spesifik (format: YYYY-MM-DD)  |
| from        | date   | Filter dari tanggal (format: YYYY-MM-DD)      |
| to          | date   | Filter sampai tanggal (format: YYYY-MM-DD)    |
| division_id | uuid   | Filter by divisi karyawan                     |
| page        | int    | Nomor halaman                                 |

**Contoh:** `GET /api/attendances?status=hadir&from=2026-02-01&to=2026-02-10&page=1`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data absensi berhasil diambil",
  "data": {
    "attendances": [
      {
        "id": "uuid",
        "employee": {
          "id": "uuid",
          "name": "John Doe",
          "position": "Senior Developer",
          "division": {
            "id": "uuid",
            "name": "Backend"
          }
        },
        "date": "2026-02-10",
        "clock_in": "08:00",
        "clock_out": "17:00",
        "status": "hadir",
        "notes": null
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 5,
    "from": 1,
    "to": 5,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

### 7.2 Get Attendance Detail

```
GET /api/attendances/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data absensi berhasil diambil",
  "data": {
    "attendance": {
      "id": "uuid",
      "employee": {
        "id": "uuid",
        "name": "John Doe",
        "position": "Senior Developer",
        "division": {
          "id": "uuid",
          "name": "Backend"
        }
      },
      "date": "2026-02-10",
      "clock_in": "08:00",
      "clock_out": "17:00",
      "status": "hadir",
      "notes": null,
      "created_at": "2026-02-10T08:00:00.000000Z",
      "updated_at": "2026-02-10T17:00:00.000000Z"
    }
  }
}
```

### 7.3 Create Attendance

```
POST /api/attendances
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "employee_id": "uuid-karyawan",
  "date": "2026-02-10",
  "clock_in": "08:00",
  "clock_out": "17:00",
  "status": "hadir",
  "notes": "Tepat waktu"
}
```

**Validation Rules:**
| Field       | Rules                                          |
|-------------|------------------------------------------------|
| employee_id | required, uuid, exists di employees            |
| date        | required, date (format: YYYY-MM-DD)            |
| clock_in    | nullable, format H:i (contoh: "08:00")         |
| clock_out   | nullable, format H:i, harus setelah clock_in   |
| status      | required, enum: `hadir`, `izin`, `sakit`, `alpha` |
| notes       | nullable, string, max:500                      |

> **Catatan**: Satu karyawan hanya bisa punya 1 absensi per tanggal. Jika sudah ada, akan error 422.

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Data absensi berhasil ditambahkan"
}
```

**Error Response (422) - Duplikat:**
```json
{
  "status": "error",
  "message": "Data absensi untuk karyawan ini pada tanggal tersebut sudah ada"
}
```

### 7.4 Update Attendance

```
PUT /api/attendances/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** Sama seperti create (section 7.3).

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data absensi berhasil diubah"
}
```

### 7.5 Delete Attendance

```
DELETE /api/attendances/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data absensi berhasil dihapus"
}
```

### 7.6 Attendance Summary (Per Bulan)

```
GET /api/attendances/summary
Authorization: Bearer {token}
```

**Query Parameters (required):**
| Param       | Type   | Keterangan                      |
|-------------|--------|---------------------------------|
| month       | int    | Bulan (1-12), **required**      |
| year        | int    | Tahun (min 2020), **required**  |
| division_id | uuid   | Filter by divisi (opsional)     |

**Contoh:** `GET /api/attendances/summary?month=2&year=2026`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Ringkasan absensi berhasil diambil",
  "data": {
    "month": 2,
    "year": 2026,
    "employees": [
      {
        "employee": {
          "id": "uuid",
          "name": "John Doe",
          "position": "Senior Developer",
          "division": "Backend"
        },
        "summary": {
          "hadir": 18,
          "izin": 1,
          "sakit": 1,
          "alpha": 0,
          "total": 20
        }
      }
    ]
  }
}
```

---

## 8. ACTIVITY LOGS

### 8.1 List Activity Logs

```
GET /api/activity-logs
Authorization: Bearer {token}
```

**Query Parameters (opsional):**
| Param      | Type   | Keterangan                                      |
|------------|--------|-------------------------------------------------|
| action     | string | Filter: `created`, `updated`, `deleted`         |
| model_type | string | Filter: `Employee`, `Division`, `User`, `Attendance` |
| user_id    | uuid   | Filter by admin yang melakukan aksi             |
| from       | date   | Filter dari tanggal (YYYY-MM-DD)                |
| to         | date   | Filter sampai tanggal (YYYY-MM-DD)              |
| page       | int    | Nomor halaman                                   |

**Contoh:** `GET /api/activity-logs?action=created&model_type=Employee&page=1`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data activity log berhasil diambil",
  "data": {
    "activity_logs": [
      {
        "id": "uuid",
        "user": {
          "id": "uuid",
          "name": "Admin",
          "username": "admin"
        },
        "action": "created",
        "model_type": "Employee",
        "model_id": "uuid",
        "description": "Menambahkan karyawan: John Doe",
        "old_values": null,
        "new_values": {
          "name": "John Doe",
          "phone": "081234567890",
          "position": "Senior Developer"
        },
        "ip_address": "127.0.0.1",
        "created_at": "2026-02-10T12:00:00.000000Z"
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 5,
    "from": 1,
    "to": 5,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

---

## Route Summary Table

| #  | Method   | Endpoint                   | Auth | Content-Type       | Keterangan                    |
|----|----------|----------------------------|------|--------------------|------------------------------ |
| 1  | POST     | /api/login                 | No   | application/json   | Login admin                   |
| 2  | POST     | /api/logout                | Yes  | -                  | Logout admin                  |
| 3  | POST     | /api/register              | Yes  | application/json   | Register admin baru           |
| 4  | GET      | /api/dashboard             | Yes  | -                  | Dashboard statistics          |
| 5  | GET      | /api/profile               | Yes  | -                  | Get profil                    |
| 6  | PUT      | /api/profile               | Yes  | application/json   | Update profil                 |
| 7  | PUT      | /api/profile/password      | Yes  | application/json   | Ganti password                |
| 8  | GET      | /api/users                 | Yes  | -                  | List admins                   |
| 9  | DELETE   | /api/users/{id}            | Yes  | -                  | Hapus admin                   |
| 10 | GET      | /api/divisions             | Yes  | -                  | List divisi                   |
| 11 | GET      | /api/divisions/{id}        | Yes  | -                  | Detail divisi                 |
| 12 | POST     | /api/divisions             | Yes  | application/json   | Tambah divisi                 |
| 13 | PUT      | /api/divisions/{id}        | Yes  | application/json   | Update divisi                 |
| 14 | DELETE   | /api/divisions/{id}        | Yes  | -                  | Hapus divisi                  |
| 15 | GET      | /api/employees             | Yes  | -                  | List karyawan                 |
| 16 | GET      | /api/employees/{id}        | Yes  | -                  | Detail karyawan               |
| 17 | POST     | /api/employees             | Yes  | multipart/form-data| Tambah karyawan               |
| 18 | POST     | /api/employees/{id}        | Yes  | multipart/form-data| Update karyawan               |
| 19 | DELETE   | /api/employees/{id}        | Yes  | -                  | Hapus karyawan                |
| 20 | POST     | /api/employees/bulk-delete | Yes  | application/json   | Hapus banyak karyawan         |
| 21 | GET      | /api/employees/summary     | Yes  | -                  | Statistik karyawan            |
| 22 | GET      | /api/employees/export      | Yes  | -                  | Export CSV                    |
| 23 | GET      | /api/attendances           | Yes  | -                  | List absensi                  |
| 24 | GET      | /api/attendances/{id}      | Yes  | -                  | Detail absensi                |
| 25 | POST     | /api/attendances           | Yes  | application/json   | Tambah absensi                |
| 26 | PUT      | /api/attendances/{id}      | Yes  | application/json   | Update absensi                |
| 27 | DELETE   | /api/attendances/{id}      | Yes  | -                  | Hapus absensi                 |
| 28 | GET      | /api/attendances/summary   | Yes  | -                  | Ringkasan absensi bulanan     |
| 29 | GET      | /api/activity-logs         | Yes  | -                  | List activity logs            |

---

## Catatan Penting untuk Frontend (Next.js)

### 1. CORS
Backend sudah dikonfigurasi menerima request dari `http://localhost:3000`. Pastikan fetch menggunakan `credentials: 'include'` jika pakai cookie-based auth, atau kirim header `Authorization` untuk token-based.

### 2. Token Storage
Simpan token dari response login di `localStorage` atau `cookie`. Contoh:
```javascript
// Simpan setelah login
localStorage.setItem('token', data.data.token);

// Gunakan di setiap request
const token = localStorage.getItem('token');
fetch(url, {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

### 3. Image URL
Field `image` pada employee berisi path relatif seperti `/storage/employees/xxx.jpg`. Untuk menampilkan gambar, gabungkan dengan base URL backend:
```javascript
const imageUrl = `http://localhost:8000${employee.image}`;
// Hasilnya: http://localhost:8000/storage/employees/xxx.jpg
```

### 4. Pagination
Gunakan query parameter `?page=X` untuk navigasi halaman. Pagination info ada di field `pagination` dalam response.

### 5. File Upload (Employee Create/Update)
- Gunakan `FormData` untuk mengirim data
- **JANGAN** set `Content-Type` header manual - biarkan browser yang handle
- Method tetap `POST` untuk create dan update (bukan PUT)

### 6. Status Absensi
Nilai yang valid: `hadir`, `izin`, `sakit`, `alpha` (huruf kecil semua).

### 7. Division Field pada Employee
Saat create/update employee, field divisi bernama `division` (bukan `division_id`), berisi UUID divisi.
