# Implementation Plan — Sub Prasojo API

## Gambaran Umum
Plan ini memetakan `docs/roadmap.md` ke instruksi teknis yang dapat di-execute oleh agent.
Total: **6 Fase**, **23 endpoint**, **~45 file baru**.

---

## Fase 1 — Foundation & Standarisasi

### 1.1 Sinkronisasi Docs
**File:** `docs/api_documentation.md`
- Tambahkan prefix `/api/v1/` pada semua endpoint
- Tambahkan section `1.6 Register` berdasarkan `RegisterRequest.php` yang sudah ada

### 1.2 ApiResponse Helper
**File baru:** `app/Http/Responses/ApiResponse.php`
```php
class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $code = 200): JsonResponse
    public static function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    public static function paginated(string $message, LengthAwarePaginator $paginator): JsonResponse
}
```
- `paginated()` harus otomatis menyertakan field `meta` sesuai PRD Bagian 3
- Semua pesan dalam Bahasa Indonesia

### 1.3 Enum AjuanStatus
**File baru:** `app/Enums/AjuanStatus.php`
- Copy persis dari PRD Bagian 4.3
- 14 case: DIAJUKAN, BELUM_DIVERIFIKASI, DIVERIFIKASI, DIPROSES, MENUNGGU_KONFIRMASI, DISETUJUI, DITOLAK, SELESAI, DIAJUKAN_TTE, TIDAK_DIPROSES, SIAP_DOWNLOAD, SIAP_DICETAK, SUDAH_DICETAK, SIAP_DIAMBIL
- 2 helper methods: `getStatusSelesai()`, `getStatusDitolak()`

### 1.4 Config SLA
**File baru:** `config/sla.php`
```php
return [
    'default_jam' => 6,
    'per_layanan' => [
        // Bisa di-override per layanan di masa depan
        // 'KTP' => 6,
    ],
];
```

### 1.5 Base Filter
**File baru:** `app/Filters/BaseFilter.php`
- Abstract class dengan method `apply(Builder $query): Builder`
- Handle: `periode_bulan`, `start_date`/`end_date`, `sort_by`/`sort_dir`, `search`
- Konversi tanggal: `dd-mm-yyyy` → Carbon
- Jika `start_date`/`end_date` dikirim, `periode_bulan` diabaikan

### 1.6 Database Index Migration
**File baru:** `database/migrations/xxxx_add_indexes_to_ajuan_table.php`
- Connection: `mysql_prasojo`
- Index pada: `ajuan_status`, `ajuan_create_datetime`, `ajuan_update_datetime`, `ajuan_no_reg`, `ajuan_kecamatan_code`, `ajuan_is_online`, `ajuan_pelapor_role_name`, `ajuan_pelapor_id`

### 1.7 Refactor Existing Controllers
- `AuthController.php` → ganti semua `response()->json()` dengan `ApiResponse`
- `UserController.php` → ganti semua `response()->json()` dengan `ApiResponse`

### 1.8 Install maatwebsite/excel
```bash
composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

---

## Fase 2 — Penyempurnaan Auth & Profile

### 2.1 Update Profile Endpoint
**File baru:** `app/Http/Requests/UpdateProfileRequest.php`
- Rules: `email` (optional, unique excluding current user), `password` (optional, min:8)

**Update:** `app/Services/UserService.php`
- Method baru: `updateProfile(SubUser $user, array $data): SubUser`
- Hash password jika dikirim

**Update:** `app/Http/Controllers/UserController.php` (atau pindah ke AuthController)
- Method baru: `updateProfile(UpdateProfileRequest $request)`

**Update:** `routes/api.php`
- `PUT /api/v1/auth/profile` di dalam `paseto.auth` group
- Pindahkan `GET /me` ke `GET /api/v1/auth/me`

### 2.2 Feature Test Auth
**File baru (5 file):**
- `tests/Feature/Auth/LoginTest.php`
- `tests/Feature/Auth/RegisterTest.php`
- `tests/Feature/Auth/LogoutTest.php`
- `tests/Feature/Auth/RefreshTokenTest.php`
- `tests/Feature/Auth/ProfileTest.php`

---

## Fase 3 — Modul Dashboard

### 3.1 Model (jika belum ada)
**File baru:** `app/Models/Ajuan.php`
- `$connection = 'mysql_prasojo'`, `$table = 'ajuan'`, `$primaryKey = 'ajuan_id'`
- `$timestamps = false`
- Relationship: `pelapor()` → `Admin`, `reviews()` → `AjuanReview`

### 3.2 DashboardFilter
**File baru:** `app/Filters/DashboardFilter.php`
- Extend BaseFilter
- Tambahan: `id_kecamatan` (filter `ajuan_kecamatan_code`), `id_layanan` (filter `ajuan_layanan_kode`)

### 3.3 DashboardService
**File baru:** `app/Services/DashboardService.php`
- `getKpi(DashboardFilter $filter)` → 4 metrics (Total Pengajuan, Selesai, Ditolak, SLA) + Indikator Tren (%) + redis cache
- `getChartTrend(DashboardFilter $filter)` → daily aggregation
- `getTopWilayah(DashboardFilter $filter)` → top 5 kecamatan

### 3.4 DashboardController
**File baru:** `app/Http/Controllers/DashboardController.php`
- `kpi()`, `chartTrend()`, `topWilayah()`

### 3.5 Redis Cache
- Key: `dashboard:kpi:{md5_of_filter_params}`
- TTL: 10 menit
- Implement di Service layer menggunakan `Cache::remember()`

### 3.6 Routes
```php
Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
    Route::get('/kpi', 'kpi');
    Route::get('/chart-trend', 'chartTrend');
    Route::get('/top-wilayah', 'topWilayah');
});
```

---

## Fase 4 — Modul Pengajuan

### 4.1 PengajuanFilter
**File baru:** `app/Filters/PengajuanFilter.php`
- `status_kategori` → mapping ke query berdasarkan tabel `lembar_kerja` atau `produk`
- `id_kecamatan`, `id_layanan`, `status`, `pelapor`, `search_no_reg`
- `pelapor` mapping: kombinasi `ajuan_is_online`, `ajuan_is_mandiri`, `ajuan_pelapor_role_name`

### 4.2 PengajuanService
**File baru:** `app/Services/PengajuanService.php`
- `getList(PengajuanFilter $filter)` → paginated query
- `export(PengajuanFilter $filter)` → return Excel download

### 4.3 PengajuanExport
**File baru:** `app/Exports/PengajuanExport.php`
- Implements `FromQuery`, `WithHeadings`, `WithStyles`
- Headings: No Reg, Layanan, Kecamatan, Pelapor, Status, Tanggal

### 4.4 PengajuanController + Routes
**File baru:** `app/Http/Controllers/PengajuanController.php`
- `index()` → `GET /api/v1/pengajuan`
- `export()` → `GET /api/v1/pengajuan/export`

---

## Fase 5 — Modul Monitoring

### 5A Operator
**File baru:** `OperatorFilter`, `OperatorService`, `OperatorController`, `OperatorRankingExport`
- Model dependency: `Admin` (connection: `mysql_prasojo`)
- KPI: total aktif (`is_active=1, level='operator'`), total berkas, rata-rata kecepatan
- Ranking: order by rata-rata kecepatan
- Detail: profil + riwayat kerja (join `ajuan` → filter by `ajuan_pelapor_id`)

### 5B Wilayah
**File baru:** `WilayahFilter`, `WilayahService`, `WilayahController`, `WilayahDistribusiExport`
- Aggregasi `GROUP BY ajuan_kecamatan_code`
- Metrics: total_ajuan, rata_rata_waktu, rasio_selesai_persen

### 5C SLA
**File baru:** `SlaFilter`, `SlaService`, `SlaController`, `SlaLayananExport`
- Target SLA dari `config('sla.default_jam')` = 6 jam
- Komparasi: aktual AVG vs target
- Status: `MEMENUHI` jika aktual <= target, `TIDAK MEMENUHI` jika aktual > target

### 5D Ulasan (TARGET CURRENT SPRINT)
**File baru:** `UlasanFilter`, `UlasanService`, `UlasanController`, `UlasanExport`
- Model dependency: `AjuanReview` (connection: `mysql_prasojo`)
- KPI: AVG(review_rating) + COUNT per bintang (1-5)
- List: join `ajuan` untuk mendapatkan `ajuan_layanan_kode`

---

## Fase 6 — Optimisasi & Finalisasi

### 6.1 Extended Caching
Terapkan Redis cache pada:
- `OperatorService::getKpiGlobal()` — TTL 10 menit
- `SlaService::getKpi()` — TTL 10 menit
- `UlasanService::getKpi()` — TTL 10 menit

### 6.2 Unit Tests
6 file: satu per Service class

### 6.3 Query Optimization
- Review semua query untuk N+1 problem
- Gunakan `with()` / `loadMissing()` untuk eager loading
- Gunakan `DB::raw()` untuk aggregation yang complex

### 6.4 Production Hardening
- Rate limiting pada auth endpoints
- Error logging yang informatif
- Deployment checklist (lihat `docs/roadmap.md` Fase 6)
