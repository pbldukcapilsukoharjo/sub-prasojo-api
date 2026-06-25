# Task Checklist — Sub Prasojo API

> **Instruksi Agent:** Mark `[/]` saat memulai task, `[x]` setelah selesai.
> Referensi detail: `.agents/implementation_plan.md`

---

## Task 1 — Foundation & Standarisasi 🔴

### Docs
- [x] Update `docs/api_documentation.md` — tambah prefix `/api/v1/` ke semua endpoint
- [x] Update `docs/api_documentation.md` — tambah section `POST /api/v1/auth/register`

### Core Infrastructure
- [x] Buat `app/Http/Responses/ApiResponse.php` — helper class (success, error, paginated)
- [x] Buat `app/Enums/AjuanStatus.php` — 14 case + 2 helper methods
- [x] Buat `config/sla.php` — default 6 jam per layanan
- [x] Buat `app/Filters/BaseFilter.php` — abstract class filter (periode, tanggal, sort, search)
- [x] Buat migration `add_indexes_to_ajuan_table` — 8 index pada tabel ajuan

### Refactor
- [x] Refactor `AuthController.php` → gunakan `ApiResponse`
- [x] Refactor `UserController.php` → gunakan `ApiResponse`

### Dependency
- [x] Install `maatwebsite/excel` + publish config

---

## Task 2 — Auth & Profile 🔴

### Endpoint Baru
- [x] Buat `app/Http/Requests/UpdateProfileRequest.php`
- [x] Tambah method `updateProfile()` di `UserService.php`
- [x] Tambah method `updateProfile()` di Controller
- [x] Registrasi route `PUT /api/v1/auth/profile`
- [x] Pindahkan route `GET /me` ke `GET /api/v1/auth/me`

### Testing
- [x] Buat `tests/Feature/Auth/LoginTest.php`
- [x] Buat `tests/Feature/Auth/RegisterTest.php`
- [x] Buat `tests/Feature/Auth/LogoutTest.php`
- [x] Buat `tests/Feature/Auth/RefreshTokenTest.php`
- [x] Buat `tests/Feature/Auth/ProfileTest.php`

---

## Task 3 — Dashboard 🟢

### Model
- [x] Buat `app/Models/Ajuan.php` (connection: mysql_prasojo)
- [x] Buat `app/Models/Admin.php` (connection: mysql_prasojo) — jika belum ada
- [x] Buat `app/Models/AjuanReview.php` (connection: mysql_prasojo) — jika belum ada

### Fitur
- [x] Buat `app/Filters/DashboardFilter.php`
- [x] Buat `app/Services/DashboardService.php` — getKpi, getChartTrend, getTopWilayah
- [x] Buat `app/Http/Controllers/DashboardController.php`
- [x] Registrasi 3 route dashboard di `api.php`
- [x] Implementasi Redis cache pada DashboardService (TTL 10 menit)

### Testing
- [x] Buat `tests/Feature/Dashboard/DashboardKpiTest.php`

---

## Fase 4: Implementasi Modul Pengajuan
- [x] Buat `app/Filters/PengajuanFilter.php`
  - [x] Implementasi filter berdasarkan kategori, wilayah, layanan, dll.
- [x] Buat `app/Exports/PengajuanExport.php`
  - [x] Konfigurasi export excel (headings, mapping data, styling).
- [x] Buat `app/Services/PengajuanService.php`
  - [x] Logika query menggunakan Eloquent (Filter, Transformasi, Pagination).
  - [x] Logika trigger export file excel.
- [x] Buat `app/Http/Controllers/PengajuanController.php`
  - [x] Endpoint `index` untuk paginasi.
  - [x] Endpoint `export` untuk download Excel.
- [x] Daftarkan route di `routes/api.php`
- [x] Buat `tests/Feature/Pengajuan/PengajuanListTest.php`

---

## Task 5A — Monitoring Operator 🟡

### Fitur
- [x] Buat `app/Filters/OperatorFilter.php`
- [x] Buat `app/Services/OperatorService.php` — getKpiGlobal, getRanking, getDetail, exportRanking
- [x] Buat `app/Exports/OperatorRankingExport.php`
- [x] Buat `app/Http/Controllers/OperatorController.php`
- [x] Registrasi 4 route operator di `api.php`

### Testing
- [x] Buat `tests/Feature/Operator/OperatorRankingTest.php`

---

## Task 5B — Monitoring Wilayah 🟡

### Fitur
- [x] Buat `app/Filters/WilayahFilter.php`
- [x] Buat `app/Services/WilayahService.php` — getDistribusi, exportDistribusi
- [x] Buat `app/Exports/WilayahDistribusiExport.php`
- [x] Buat `app/Http/Controllers/WilayahController.php`
- [x] Registrasi 2 route wilayah di `api.php`

### Testing
- [x] Buat `tests/Feature/Wilayah/WilayahDistribusiTest.php`

---

## Task 5C — SLA Monitoring 🟡

### Fitur
- [ ] Buat `app/Filters/SlaFilter.php`
- [ ] Buat `app/Services/SlaService.php` — getKpi, getLayanan, exportLayanan
- [ ] Buat `app/Exports/SlaLayananExport.php`
- [ ] Buat `app/Http/Controllers/SlaController.php`
- [ ] Registrasi 3 route SLA di `api.php`

### Testing
- [ ] Buat `tests/Feature/Sla/SlaKpiTest.php`

---

## Task 5D — Monitoring Ulasan 🟡

### Fitur
- [ ] Buat `app/Filters/UlasanFilter.php`
- [ ] Buat `app/Services/UlasanService.php` — getKpi, getList, exportUlasan
- [ ] Buat `app/Exports/UlasanExport.php`
- [ ] Buat `app/Http/Controllers/UlasanController.php`
- [ ] Registrasi 3 route ulasan di `api.php`

### Testing
- [ ] Buat `tests/Feature/Ulasan/UlasanKpiTest.php`

---

## Task 6 — Optimisasi & Finalisasi 🟢

### Caching
- [ ] Redis cache pada `OperatorService::getKpiGlobal()`
- [ ] Redis cache pada `SlaService::getKpi()`
- [ ] Redis cache pada `UlasanService::getKpi()`

### Unit Tests
- [ ] Buat `tests/Unit/Services/DashboardServiceTest.php`
- [ ] Buat `tests/Unit/Services/PengajuanServiceTest.php`
- [ ] Buat `tests/Unit/Services/OperatorServiceTest.php`
- [ ] Buat `tests/Unit/Services/WilayahServiceTest.php`
- [ ] Buat `tests/Unit/Services/SlaServiceTest.php`
- [ ] Buat `tests/Unit/Services/UlasanServiceTest.php`

### Production Hardening
- [ ] Review & optimasi query N+1 (Eager Loading)
- [ ] Rate limiting pada endpoint auth
- [ ] Final sync `docs/api_documentation.md` dengan implementasi
- [ ] Error logging review
- [ ] Deployment checklist verified
