# Task Checklist — Sub Prasojo API

> **Instruksi Agent:** Mark `[/]` saat memulai task, `[x]` setelah selesai.
> Referensi detail: `.agents/implementation_plan.md`

---

## Fase 1 — Foundation & Standarisasi 🔴

### Docs
- [ ] Update `docs/api_documentation.md` — tambah prefix `/api/v1/` ke semua endpoint
- [ ] Update `docs/api_documentation.md` — tambah section `POST /api/v1/auth/register`

### Core Infrastructure
- [ ] Buat `app/Http/Responses/ApiResponse.php` — helper class (success, error, paginated)
- [ ] Buat `app/Enums/AjuanStatus.php` — 14 case + 2 helper methods
- [ ] Buat `config/sla.php` — default 6 jam per layanan
- [ ] Buat `app/Filters/BaseFilter.php` — abstract class filter (periode, tanggal, sort, search)
- [ ] Buat migration `add_indexes_to_ajuan_table` — 8 index pada tabel ajuan

### Refactor
- [ ] Refactor `AuthController.php` → gunakan `ApiResponse`
- [ ] Refactor `UserController.php` → gunakan `ApiResponse`

### Dependency
- [ ] Install `maatwebsite/excel` + publish config

---

## Fase 2 — Auth & Profile 🔴

### Endpoint Baru
- [ ] Buat `app/Http/Requests/UpdateProfileRequest.php`
- [ ] Tambah method `updateProfile()` di `UserService.php`
- [ ] Tambah method `updateProfile()` di Controller
- [ ] Registrasi route `PUT /api/v1/auth/profile`
- [ ] Pindahkan route `GET /me` ke `GET /api/v1/auth/me`

### Testing
- [ ] Buat `tests/Feature/Auth/LoginTest.php`
- [ ] Buat `tests/Feature/Auth/RegisterTest.php`
- [ ] Buat `tests/Feature/Auth/LogoutTest.php`
- [ ] Buat `tests/Feature/Auth/RefreshTokenTest.php`
- [ ] Buat `tests/Feature/Auth/ProfileTest.php`

---

## Fase 3 — Dashboard 🔴

### Model
- [ ] Buat `app/Models/Ajuan.php` (connection: mysql_prasojo)
- [ ] Buat `app/Models/Admin.php` (connection: mysql_prasojo) — jika belum ada
- [ ] Buat `app/Models/AjuanReview.php` (connection: mysql_prasojo) — jika belum ada

### Fitur
- [ ] Buat `app/Filters/DashboardFilter.php`
- [ ] Buat `app/Services/DashboardService.php` — getKpi, getChartTrend, getTopWilayah
- [ ] Buat `app/Http/Controllers/DashboardController.php`
- [ ] Registrasi 3 route dashboard di `api.php`
- [ ] Implementasi Redis cache pada DashboardService (TTL 10 menit)

### Testing
- [ ] Buat `tests/Feature/Dashboard/DashboardKpiTest.php`

---

## Fase 4 — Pengajuan 🟡

### Fitur
- [ ] Buat `app/Filters/PengajuanFilter.php` — status_kategori, kecamatan, layanan, pelapor
- [ ] Buat `app/Services/PengajuanService.php` — getList, export
- [ ] Buat `app/Exports/PengajuanExport.php` — FromQuery, WithHeadings, WithStyles
- [ ] Buat `app/Http/Controllers/PengajuanController.php`
- [ ] Registrasi 2 route pengajuan di `api.php`

### Testing
- [ ] Buat `tests/Feature/Pengajuan/PengajuanListTest.php`

---

## Fase 5A — Monitoring Operator 🟡

### Fitur
- [ ] Buat `app/Filters/OperatorFilter.php`
- [ ] Buat `app/Services/OperatorService.php` — getKpiGlobal, getRanking, getDetail, exportRanking
- [ ] Buat `app/Exports/OperatorRankingExport.php`
- [ ] Buat `app/Http/Controllers/OperatorController.php`
- [ ] Registrasi 4 route operator di `api.php`

### Testing
- [ ] Buat `tests/Feature/Operator/OperatorRankingTest.php`

---

## Fase 5B — Monitoring Wilayah 🟡

### Fitur
- [ ] Buat `app/Filters/WilayahFilter.php`
- [ ] Buat `app/Services/WilayahService.php` — getDistribusi, exportDistribusi
- [ ] Buat `app/Exports/WilayahDistribusiExport.php`
- [ ] Buat `app/Http/Controllers/WilayahController.php`
- [ ] Registrasi 2 route wilayah di `api.php`

### Testing
- [ ] Buat `tests/Feature/Wilayah/WilayahDistribusiTest.php`

---

## Fase 5C — SLA Monitoring 🟡

### Fitur
- [ ] Buat `app/Filters/SlaFilter.php`
- [ ] Buat `app/Services/SlaService.php` — getKpi, getLayanan, exportLayanan
- [ ] Buat `app/Exports/SlaLayananExport.php`
- [ ] Buat `app/Http/Controllers/SlaController.php`
- [ ] Registrasi 3 route SLA di `api.php`

### Testing
- [ ] Buat `tests/Feature/Sla/SlaKpiTest.php`

---

## Fase 5D — Monitoring Ulasan 🟡

### Fitur
- [ ] Buat `app/Filters/UlasanFilter.php`
- [ ] Buat `app/Services/UlasanService.php` — getKpi, getList, exportUlasan
- [ ] Buat `app/Exports/UlasanExport.php`
- [ ] Buat `app/Http/Controllers/UlasanController.php`
- [ ] Registrasi 3 route ulasan di `api.php`

### Testing
- [ ] Buat `tests/Feature/Ulasan/UlasanKpiTest.php`

---

## Fase 6 — Optimisasi & Finalisasi 🟢

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
