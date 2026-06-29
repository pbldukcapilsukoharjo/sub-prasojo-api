# Roadmap Pengembangan Backend API
**Proyek:** Sistem Monitoring Layanan Disdukcapil (Dashboard Eksekutif)  
**Stack:** Laravel 12 · PHP 8.2+ · PASETO Auth · MariaDB · Redis  
**Tanggal Disusun:** 24 Juni 2026  
**Referensi:** [PRD.md](./PRD.md) · [database_schema.md](./database_schema.md) · [api_documentation.md](./api_documentation.md)

---

## Status Progress Saat Ini

### ✅ Sudah Selesai
| Komponen | Detail |
|----------|--------|
| Project Setup | Laravel 12, Dockerfile, `.env` configuration |
| Migration `sub_users` | Tabel user khusus dashboard (`monitoring_users`) |
| Migration `refresh_tokens` | Tabel penyimpanan refresh token PASETO |
| Model `SubUser` | Eloquent model untuk user dashboard |
| Model `RefreshToken` | Eloquent model untuk refresh token |
| `PasetoService` | Service enkripsi/dekripsi PASETO token |
| `AuthService` | Service login, register, logout, refresh |
| `UserService` | Service get user profile |
| `AuthController` | Handler auth (login, register, logout, refresh) |
| `UserController` | Handler get profile (`/me`) |
| `LoginRequest` | Form Request validation login |
| `RegisterRequest` | Form Request validation register |
| `PasetoAuth` Middleware | Middleware verifikasi token pada protected routes |
| Routing `api.php` | Route group `/api/v1/auth/*` dan `/api/v1/me` |

### ❌ Belum Dikerjakan
- Standarisasi response (JsonResponse helper class)
- Enums (AjuanStatus, dsb.)
- Filter layer (base class & implementasi per modul)
- Update Profile endpoint
- Seluruh Modul Dashboard, Pengajuan, Operator, Wilayah, SLA, Ulasan
- Export Excel (.xlsx)
- Database Indexing pada tabel `ajuan`
- Redis Caching untuk KPI
- Testing (Unit & Feature)
- Sinkronisasi docs dengan prefix `/api/v1/`

---

## Keputusan Teknis yang Sudah Disepakati

| # | Keputusan | Pilihan |
|---|-----------|---------|
| 1 | URL Prefix | Menggunakan versioning `/api/v1/...` — docs akan disesuaikan |
| 2 | Endpoint Register | Dipertahankan sebagai endpoint resmi, ditambahkan ke docs |
| 3 | Target SLA Default | Seluruh layanan default **6 jam** (hardcode di Config/Enum) |
| 4 | Library Export Excel | `maatwebsite/excel` — native Laravel, fitur styling lengkap |
| 5 | Caching | Redis (sudah terpasang di environment) |
| 6 | Testing | Unit Test + Feature Test (minimal per modul) |

---

## Fase Pengembangan

### Fase 1 — Foundation & Standarisasi
> **Prioritas:** 🔴 Tinggi  
> **Tujuan:** Menyiapkan pondasi arsitektur yang akan dipakai ulang oleh semua modul.

- [ ] **Sinkronisasi Docs** — Update `api_documentation.md` agar semua endpoint memakai prefix `/api/v1/`
- [ ] **Sinkronisasi Docs** — Tambahkan endpoint `POST /api/v1/auth/register` ke `api_documentation.md`
- [ ] **JsonResponse Helper** — Buat class `App\Http\Responses\ApiResponse` dengan method statis: `success()`, `error()`, `paginated()`
  - Format output sesuai standar PRD Bagian 3 (Bahasa Indonesia)
- [ ] **Enum `AjuanStatus`** — Buat `App\Enums\AjuanStatus` sesuai spesifikasi PRD Bagian 4.3
  - Termasuk helper method: `getStatusSelesai()`, `getStatusMenunggu()`
- [ ] **Config SLA** — Buat file config `config/sla.php` dengan default 6 jam untuk semua layanan
  - Struktur: `['KTP' => 6, 'AKTA_KELAHIRAN' => 6, ...]` (dapat di-override per layanan di masa depan)
- [ ] **Base Filter Class** — Buat `App\Filters\BaseFilter` sebagai abstract class
  - Handle: `periode_bulan`, `start_date/end_date`, `sort_by/sort_dir`, `search`
  - Format tanggal input: `dd-mm-yyyy`
- [ ] **Database Indexing** — Buat migration untuk menambahkan index pada tabel `ajuan`:
  - `ajuan_status`, `ajuan_create_datetime`, `ajuan_update_datetime`
  - `ajuan_no_reg`, `ajuan_kecamatan_code`
  - `ajuan_is_online`, `ajuan_pelapor_role_name`, `ajuan_pelapor_id`
- [ ] **Refactor Existing** — Terapkan `ApiResponse` helper ke `AuthController` dan `UserController` yang sudah ada
- [ ] **Install Dependency** — `composer require maatwebsite/excel` dan publish config

---

### Fase 2 — Penyempurnaan Auth & Profile
> **Prioritas:** 🔴 Tinggi  
> **Tujuan:** Melengkapi modul Auth sesuai API Documentation.

- [ ] **`PUT /api/v1/auth/profile`** — Endpoint update profile
  - Buat `UpdateProfileRequest` (validasi email unique, password optional)
  - Tambahkan method `updateProfile()` di `UserService`
  - Tambahkan method `updateProfile()` di `UserController` (atau `AuthController`)
  - Registrasi route di `api.php` dalam middleware `paseto.auth`
- [ ] **Perapian Route** — Pindahkan `GET /me` ke group `/auth/me` agar konsisten dengan docs (`GET /api/v1/auth/me`)
- [ ] **Feature Test Auth** — Buat test:
  - `tests/Feature/Auth/LoginTest.php`
  - `tests/Feature/Auth/RegisterTest.php`
  - `tests/Feature/Auth/LogoutTest.php`
  - `tests/Feature/Auth/RefreshTokenTest.php`
  - `tests/Feature/Auth/ProfileTest.php`

---

### Fase 3 — Modul Dashboard
> **Prioritas:** 🔴 Tinggi  
> **Tujuan:** Core dashboard eksekutif — KPI cards, line chart trend, bar chart wilayah.

- [ ] **`DashboardFilter`** — Extend `BaseFilter`, tambahkan: `id_kecamatan`, `id_layanan`
- [ ] **`DashboardService`** — Business logic:
  - `getKpi()` — hitung Total Pengajuan, Selesai, Ditolak, Rata-rata SLA, dan Indikator Tren (%)
    - Gunakan `AjuanStatus::getStatusSelesai()` dan `AjuanStatus::getStatusDitolak()`
    - Rata-rata SLA = `AVG(ajuan_update_datetime - ajuan_create_datetime)` untuk status selesai
    - Trend dihitung berdasarkan periode sebelumnya dengan rumus: `((Nilai Saat Ini - Nilai Sebelumnya) / Nilai Sebelumnya) * 100`
    - Format output: jam numerik (`rata_rata_sla_jam`) + teks (`rata_rata_sla_text`) + trend percentage untuk setiap card
  - `getChartTrend()` — agregasi harian: `total_ajuan`, `selesai`
  - `getTopWilayah()` — Top 5 kecamatan berdasarkan volume ajuan
- [ ] **`DashboardController`** — 3 endpoint:
  - `GET /api/v1/dashboard/kpi`
  - `GET /api/v1/dashboard/chart-trend`
  - `GET /api/v1/dashboard/top-wilayah`
- [ ] **Redis Cache** — Terapkan caching pada `DashboardService`:
  - Key pattern: `dashboard:kpi:{filter_hash}`
  - TTL: 5–15 menit (sesuaikan dengan frekuensi data berubah)
  - Cache invalidation: time-based expiry (tidak perlu event-driven karena read-only)
- [ ] **Feature Test Dashboard** — `tests/Feature/Dashboard/DashboardKpiTest.php`, dsb.

---

### Fase 4 — Modul Pengajuan
> **Prioritas:** 🟡 Sedang  
> **Tujuan:** Tabel master pengajuan dengan filter lengkap dan export Excel.

- [ ] **`PengajuanFilter`** — Extend `BaseFilter`, tambahkan:
  - `status_kategori` (lembar_kerja / produk / all)
  - `id_kecamatan`, `id_layanan`, `status`, `pelapor`, `search_no_reg`
  - Mapping `pelapor` ke kombinasi kolom: `ajuan_is_online`, `ajuan_is_mandiri`, `ajuan_pelapor_role_name`
- [ ] **`PengajuanService`** — Business logic:
  - `getList()` — query tabel `ajuan` dengan filter & pagination
  - `exportExcel()` — generate file `.xlsx` menggunakan `maatwebsite/excel`
- [ ] **`PengajuanController`** — 2 endpoint:
  - `GET /api/v1/pengajuan`
  - `GET /api/v1/pengajuan/export`
- [ ] **`PengajuanExport`** — Class export `maatwebsite/excel` (implements `FromQuery`, `WithHeadings`, `WithStyles`)
- [ ] **Feature Test Pengajuan** — `tests/Feature/Pengajuan/PengajuanListTest.php`

---

### Fase 5 — Modul Monitoring (Operator, Wilayah, SLA, Ulasan)
> **Prioritas:** 🟡 Sedang  
> **Tujuan:** 4 sub-modul monitoring dengan masing-masing KPI, tabel data, dan export.

#### 5A — Monitoring Operator
- [ ] **`OperatorFilter`** — filter standar + `id_kecamatan` + `search_nama` (tanpa `id_layanan`)
- [ ] **`OperatorService`** — Business logic:
  - `getKpiGlobal()` — Total Aktif, Total Berkas, Rata-rata Kecepatan
    - Operator aktif: `admin` dengan `is_active=1` dan `level='operator'`
    - Kecepatan: `AVG(ajuan_update_datetime - ajuan_create_datetime)` per operator
  - `getRanking()` — urutan berdasarkan rata-rata kecepatan (pagination)
  - `getDetail($idOperator)` — profil + riwayat kerja operator
  - `exportRanking()` — export Excel
- [ ] **`OperatorController`** — 4 endpoint:
  - `GET /api/v1/operator/kpi-global`
  - `GET /api/v1/operator/ranking`
  - `GET /api/v1/operator/{id_operator}/detail`
  - `GET /api/v1/operator/export`
- [ ] **Feature Test Operator** — `tests/Feature/Operator/OperatorRankingTest.php`, dsb.

#### 5B — Monitoring Wilayah
- [ ] **`WilayahFilter`** — filter standar + `id_kecamatan` (tanpa `search`, tanpa `id_layanan`)
- [ ] **`WilayahService`** — Business logic:
  - `getDistribusi()` — volume per kecamatan + rata-rata waktu + rasio selesai (pagination)
  - `exportDistribusi()` — export Excel
- [ ] **`WilayahController`** — 2 endpoint:
  - `GET /api/v1/wilayah/distribusi`
  - `GET /api/v1/wilayah/export`
- [ ] **Feature Test Wilayah**

#### 5C — SLA Monitoring
- [ ] **`SlaFilter`** — filter standar + `id_kecamatan`
- [ ] **`SlaService`** — Business logic:
  - `getKpi()` — Rata-rata Waktu Proses Global + Persentase Pencapaian SLA
    - Komparasi aktual vs target dari `config/sla.php` (default 6 jam)
  - `getLayanan()` — tabel per layanan: target SLA, aktual rata-rata, status (MEMENUHI/TIDAK MEMENUHI)
  - `exportLayanan()` — export Excel
- [ ] **`SlaController`** — 3 endpoint:
  - `GET /api/v1/sla/kpi`
  - `GET /api/v1/sla/layanan`
  - `GET /api/v1/sla/export`
- [ ] **Feature Test SLA**

#### 5D — Monitoring Ulasan
- [ ] **`UlasanFilter`** — filter standar + `id_layanan` + `rating`
- [ ] **`UlasanService`** — Business logic:
  - `getKpi()` — rata-rata bintang + distribusi per bintang (1-5) untuk Donut Chart
    - Source: tabel `ajuan_review` (JOIN `ajuan` untuk filter layanan)
  - `getList()` — tabel komentar warga (pagination)
  - `exportUlasan()` — export data ulasan ke Excel (.xlsx) dengan filter rentang tanggal, layanan, dan rating
- [ ] **`UlasanExport`** — Class export `maatwebsite/excel` (implements `FromQuery`, `WithHeadings`, `WithStyles`)
  - Filename pattern: `export_ulasan_{start_date}_{end_date}.xlsx`
- [ ] **`UlasanController`** — 3 endpoint:
  - `GET /api/v1/ulasan/kpi`
  - `GET /api/v1/ulasan/list`
  - `GET /api/v1/ulasan/export`
- [ ] **Feature Test Ulasan**

---

### Fase 6 — Optimisasi, Testing & Finalisasi
> **Prioritas:** 🟢 Normal  
> **Tujuan:** Polish, performance, dan kesiapan production.

- [ ] **Redis Cache** — Terapkan caching pada endpoint KPI lain yang sering diakses:
  - `operator/kpi-global`, `sla/kpi`, `ulasan/kpi`
- [ ] **Unit Test Service** — Buat unit test untuk setiap Service class:
  - `tests/Unit/Services/DashboardServiceTest.php`
  - `tests/Unit/Services/PengajuanServiceTest.php`
  - `tests/Unit/Services/OperatorServiceTest.php`
  - `tests/Unit/Services/WilayahServiceTest.php`
  - `tests/Unit/Services/SlaServiceTest.php`
  - `tests/Unit/Services/UlasanServiceTest.php`
- [ ] **Query Optimization** — Review dan optimasi query N+1 menggunakan Eager Loading
- [ ] **Rate Limiting** — Terapkan throttle pada endpoint auth (login/register)
- [ ] **API Documentation Final** — Pastikan seluruh docs sinkron dengan implementasi akhir
- [ ] **Error Logging** — Pastikan semua exception di-log dengan konteks yang cukup
- [ ] **Deployment Checklist**:
  - [ ] `APP_ENV=production`, `APP_DEBUG=false`
  - [ ] Redis connection terverifikasi
  - [ ] Database indexes terverifikasi
  - [ ] PASETO secret key berbeda dari development

---

## Ringkasan Endpoint (23 Total)

| # | Method | Endpoint | Modul | Fase |
|---|--------|----------|-------|------|
| 1 | POST | `/api/v1/auth/register` | Auth | ✅ Done |
| 2 | POST | `/api/v1/auth/login` | Auth | ✅ Done |
| 3 | POST | `/api/v1/auth/logout` | Auth | ✅ Done |
| 4 | POST | `/api/v1/auth/refresh` | Auth | ✅ Done |
| 5 | GET | `/api/v1/auth/me` | Auth | Fase 2 |
| 6 | PUT | `/api/v1/auth/profile` | Auth | Fase 2 |
| 7 | GET | `/api/v1/dashboard/kpi` | Dashboard | Fase 3 |
| 8 | GET | `/api/v1/dashboard/chart-trend` | Dashboard | Fase 3 |
| 9 | GET | `/api/v1/dashboard/top-wilayah` | Dashboard | Fase 3 |
| 10 | GET | `/api/v1/pengajuan` | Pengajuan | Fase 4 |
| 11 | GET | `/api/v1/pengajuan/export` | Pengajuan | Fase 4 |
| 12 | GET | `/api/v1/operator/kpi-global` | Operator | Fase 5A |
| 13 | GET | `/api/v1/operator/ranking` | Operator | Fase 5A |
| 14 | GET | `/api/v1/operator/{id}/detail` | Operator | Fase 5A |
| 15 | GET | `/api/v1/operator/export` | Operator | Fase 5A |
| 16 | GET | `/api/v1/wilayah/distribusi` | Wilayah | Fase 5B |
| 17 | GET | `/api/v1/wilayah/export` | Wilayah | Fase 5B |
| 18 | GET | `/api/v1/sla/kpi` | SLA | Fase 5C |
| 19 | GET | `/api/v1/sla/layanan` | SLA | Fase 5C |
| 20 | GET | `/api/v1/sla/export` | SLA | Fase 5C |
| 21 | GET | `/api/v1/ulasan/kpi` | Ulasan | Fase 5D |
| 22 | GET | `/api/v1/ulasan/list` | Ulasan | Fase 5D |
| 23 | GET | `/api/v1/ulasan/export` | Ulasan | Fase 5D |

---

## Arsitektur File yang Akan Dibuat

```
app/
├── Enums/
│   └── AjuanStatus.php                    # Fase 1
├── Filters/
│   ├── BaseFilter.php                     # Fase 1
│   ├── DashboardFilter.php                # Fase 3
│   ├── PengajuanFilter.php                # Fase 4
│   ├── OperatorFilter.php                 # Fase 5A
│   ├── WilayahFilter.php                  # Fase 5B
│   ├── SlaFilter.php                      # Fase 5C
│   └── UlasanFilter.php                   # Fase 5D
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php             # ✅ (refactor Fase 1)
│   │   ├── UserController.php             # ✅ (refactor Fase 2)
│   │   ├── DashboardController.php        # Fase 3
│   │   ├── PengajuanController.php        # Fase 4
│   │   ├── OperatorController.php         # Fase 5A
│   │   ├── WilayahController.php          # Fase 5B
│   │   ├── SlaController.php              # Fase 5C
│   │   └── UlasanController.php           # Fase 5D
│   ├── Requests/
│   │   ├── LoginRequest.php               # ✅
│   │   ├── RegisterRequest.php            # ✅
│   │   └── UpdateProfileRequest.php       # Fase 2
│   └── Responses/
│       └── ApiResponse.php                # Fase 1
├── Exports/
│   ├── PengajuanExport.php                # Fase 4
│   ├── OperatorRankingExport.php          # Fase 5A
│   ├── WilayahDistribusiExport.php        # Fase 5B
│   ├── SlaLayananExport.php               # Fase 5C
│   └── UlasanExport.php                   # Fase 5D
├── Services/
│   ├── AuthService.php                    # ✅
│   ├── PasetoService.php                  # ✅
│   ├── UserService.php                    # ✅ (extend Fase 2)
│   ├── DashboardService.php               # Fase 3
│   ├── PengajuanService.php               # Fase 4
│   ├── OperatorService.php                # Fase 5A
│   ├── WilayahService.php                 # Fase 5B
│   ├── SlaService.php                     # Fase 5C
│   └── UlasanService.php                  # Fase 5D

config/
└── sla.php                                # Fase 1

tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php                  # Fase 2
│   │   ├── RegisterTest.php               # Fase 2
│   │   ├── LogoutTest.php                 # Fase 2
│   │   ├── RefreshTokenTest.php           # Fase 2
│   │   └── ProfileTest.php               # Fase 2
│   ├── Dashboard/
│   │   └── DashboardKpiTest.php           # Fase 3
│   ├── Pengajuan/
│   │   └── PengajuanListTest.php          # Fase 4
│   ├── Operator/
│   │   └── OperatorRankingTest.php        # Fase 5A
│   ├── Wilayah/
│   │   └── WilayahDistribusiTest.php      # Fase 5B
│   ├── Sla/
│   │   └── SlaKpiTest.php                 # Fase 5C
│   └── Ulasan/
│       └── UlasanKpiTest.php              # Fase 5D
└── Unit/
    └── Services/
        ├── DashboardServiceTest.php       # Fase 6
        ├── PengajuanServiceTest.php       # Fase 6
        ├── OperatorServiceTest.php        # Fase 6
        ├── WilayahServiceTest.php         # Fase 6
        ├── SlaServiceTest.php             # Fase 6
        └── UlasanServiceTest.php          # Fase 6
```

---

## Catatan Penting

1. **Read-Only System** — Sistem ini hanya membaca data dari database `prasojo` (skema lama). Satu-satunya operasi *write* adalah pada tabel `sub_users` dan `refresh_tokens` untuk manajemen akses dashboard.

2. **Koneksi Database Ganda** — Kemungkinan perlu konfigurasi 2 database connection di `config/database.php`:
   - `mysql` (default) → database dashboard (`sub_users`, `refresh_tokens`)
   - `mysql_prasojo` → database operasional (`ajuan`, `admin`, `ajuan_review`, dll)

3. **Tidak Ada Model untuk Tabel Lama** — Tabel `ajuan`, `admin`, `ajuan_review`, dll. dari database `prasojo` perlu dibuatkan Eloquent Model dengan `$connection = 'mysql_prasojo'` dan `$timestamps = false` (karena format kolom timestamp berbeda).

4. **Format Tanggal** — Perhatikan bahwa kolom datetime di database lama menggunakan format `datetime` MariaDB standar, namun parameter filter API menggunakan format `dd-mm-yyyy`. Konversi harus dilakukan di Filter layer.
