# Modul CRUD Master Hari Libur Nasional + Import Excel

Implementasi endpoint API untuk mengelola data Hari Libur Nasional secara dinamis (CRUD + Bulk Input + Import Excel), sesuai kebutuhan di [problem-statement-sla-dinamis.md](file:///c:/projects/laravel/sub-prasojo-api/docs/problem-statement-sla-dinamis.md#L23-L30). Saat ini, tabel `master_libur_nasionals` sudah ada (migration, model, seeder) dan sudah dikonsumsi oleh [SLACalculator.php](file:///c:/projects/laravel/sub-prasojo-api/app/Services/SLACalculator.php), tetapi **belum ada endpoint API** untuk CRUD oleh user.

## User Review Required

> [!IMPORTANT]
> - Endpoint import Excel akan melakukan **full rollback** jika ditemukan tanggal duplikat (baik duplikat di dalam file maupun duplikat terhadap data existing di DB). Ini memastikan integritas data: **semua berhasil atau tidak ada yang masuk sama sekali**.
> - Template Excel disediakan sebagai endpoint download terpisah (`GET /api/v1/holidays/template`), bukan file statis.

> [!WARNING]
> - Endpoint `DELETE /api/v1/holidays/{id}` menghapus hari libur dari tabel. Jika hari libur dihapus, **kalkulasi SLA perlu di-recalculate** (`POST /api/v1/sla/recalculate`) agar data historis akurat. Apakah perlu auto-trigger recalculate setelah setiap mutasi data libur, atau biarkan user trigger manual?

---

## Git Branching & Deployment Strategy

### Branch
- **Parent branch:** `staging-amru`
- **Feature branch:** `feature/crud-master-hari-libur-byamru`

### Alur Git
1. Checkout dari `staging-amru` → buat branch `feature/crud-master-hari-libur-byamru`
2. Implementasi kode di branch fitur
3. Commit dengan pesan bahasa Indonesia menggunakan Conventional Commits
4. Push branch fitur ke remote
5. Buat Pull Request dari `feature/crud-master-hari-libur-byamru` → `staging-amru`
   - Judul PR: `feat: implementasi modul CRUD master hari libur nasional + import excel`
6. Merge PR ke `staging-amru`
7. Buat Pull Request dari `staging-amru` → `staging`
   - Judul PR: `feat: modul master hari libur nasional (CRUD + import excel)`

### Konvensi Commit (Bahasa Indonesia)
- `feat: tambah service dan validasi request modul hari libur`
- `feat: tambah import dan template excel hari libur`
- `feat: tambah controller dan route API holidays`
- `test: tambah pengujian fitur CRUD dan import hari libur`
- `docs: tambah dokumentasi endpoint master hari libur`

---

## Proposed Changes

### Komponen 1: Service Layer

#### [NEW] `app/Services/HolidayService.php`
Service utama yang menangani semua logika bisnis Master Hari Libur:
- `index(array $filters)` — List hari libur dengan filter `tahun` dan `search`, dengan paginasi.
- `store(array $data)` — Simpan 1 atau banyak hari libur sekaligus (bulk create). Validasi duplikasi tanggal di-level service dengan `DB::transaction()`.
- `show(int $id)` — Detail satu hari libur.
- `update(int $id, array $data)` — Update tanggal/keterangan satu hari libur.
- `destroy(int $id)` — Hapus satu hari libur.
- `destroyBulk(array $ids)` — Hapus banyak hari libur sekaligus.
- `importFromExcel(UploadedFile $file)` — Proses import Excel:
  1. Parse file dengan `Maatwebsite\Excel`.
  2. Validasi format setiap baris (tanggal valid, keterangan tidak kosong).
  3. Cek duplikat internal (antar baris di dalam file).
  4. Cek duplikat terhadap DB existing.
  5. Jika ada duplikat di manapun → **throw exception, rollback seluruh transaksi**.
  6. Jika lolos → bulk insert semua baris dalam satu transaksi.
- `generateTemplate()` — Generate file Excel template dengan header dan contoh data.

---

### Komponen 2: Form Request Validation

#### [NEW] `app/Http/Requests/Holiday/StoreHolidayRequest.php`
Validasi untuk create (single & bulk):
```
holidays            → required|array|min:1
holidays.*.tanggal  → required|date_format:Y-m-d
holidays.*.keterangan → required|string|max:255
```
Menolak jika ada tanggal duplikat di dalam array input itu sendiri (validasi custom `distinct` pada `holidays.*.tanggal`).

#### [NEW] `app/Http/Requests/Holiday/UpdateHolidayRequest.php`
Validasi untuk update single entry:
```
tanggal     → required|date_format:Y-m-d|unique:master_libur_nasionals,tanggal,{id}
keterangan  → required|string|max:255
```

#### [NEW] `app/Http/Requests/Holiday/ImportHolidayRequest.php`
Validasi untuk import Excel:
```
file → required|file|mimes:xlsx,xls|max:2048
```

#### [NEW] `app/Http/Requests/Holiday/IndexHolidayRequest.php`
Validasi filter list:
```
tahun    → nullable|integer|min:2020|max:2099
search   → nullable|string|max:100
page     → nullable|integer|min:1
per_page → nullable|integer|min:1|max:100
```

#### [NEW] `app/Http/Requests/Holiday/DestroyBulkHolidayRequest.php`
Validasi bulk delete:
```
ids → required|array|min:1
ids.* → required|integer|exists:master_libur_nasionals,id
```

---

### Komponen 3: Import & Export Excel (Maatwebsite)

#### [NEW] `app/Imports/HolidayImport.php`
Import class menggunakan `Maatwebsite\Excel`:
- Implements `ToCollection` untuk kontrol penuh atas proses parsing.
- Membaca kolom: `tanggal` (kolom A), `keterangan` (kolom B).
- Melakukan validasi per baris dan mengumpulkan error rows.
- **Tidak langsung insert** — mengembalikan Collection parsed rows ke Service yang akan melakukan insert di dalam transaksi.

#### [NEW] `app/Exports/HolidayTemplateExport.php`
Export class untuk generate template kosong:
- Header: `Tanggal (YYYY-MM-DD)`, `Keterangan`.
- 2 contoh baris: `2027-01-01, Tahun Baru 2027 Masehi` dan `2027-08-17, Proklamasi Kemerdekaan`.
- Auto-size kolom, bold header, format kolom A sebagai teks agar tanggal tidak ter-convert oleh Excel.

---

### Komponen 4: Controller

#### [NEW] `app/Http/Controllers/Api/V1/HolidayController.php`
Controller REST dengan dependency injection `HolidayService`:

| Method | Route | Deskripsi |
|--------|-------|-----------|
| `index` | `GET /api/v1/holidays` | List + filter tahun + search + paginasi |
| `store` | `POST /api/v1/holidays` | Bulk create (1 atau banyak) |
| `show` | `GET /api/v1/holidays/{id}` | Detail satu hari libur |
| `update` | `PUT /api/v1/holidays/{id}` | Update satu hari libur |
| `destroy` | `DELETE /api/v1/holidays/{id}` | Hapus satu hari libur |
| `destroyBulk` | `DELETE /api/v1/holidays/bulk` | Hapus banyak hari libur |
| `import` | `POST /api/v1/holidays/import` | Import dari file Excel |
| `template` | `GET /api/v1/holidays/template` | Download template Excel |

Semua method dibungkus `try-catch` dengan `Log::error()` sesuai pola controller yang sudah ada ([SLAController.php](file:///c:/projects/laravel/sub-prasojo-api/app/Http/Controllers/Api/V1/SLAController.php)). Response menggunakan [ApiResponse](file:///c:/projects/laravel/sub-prasojo-api/app/Http/Responses/ApiResponse.php).

---

### Komponen 5: Route Registration

#### [MODIFY] `routes/api.php`
Menambahkan route group baru di bawah grup `operational-hours`:

```php
/*
|--------------------------------------------------------------------------
| Master Hari Libur Nasional
|--------------------------------------------------------------------------
*/
Route::prefix('holidays')->middleware('paseto.auth')
    ->controller(HolidayController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/template', 'template');
        Route::post('/import', 'import');
        Route::delete('/bulk', 'destroyBulk');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
```

> [!NOTE]
> Route `/template`, `/import`, dan `/bulk` **harus didaftarkan SEBELUM** `/{id}` agar tidak tertangkap oleh wildcard parameter.

---

### Komponen 6: Update API Documentation

#### [MODIFY] `docs/api_documentation.md`
Menambahkan section **"8. Modul Master Hari Libur"** dengan dokumentasi endpoint, request/response body, dan query parameters untuk seluruh endpoint holiday.

---

## Verification Plan

### Automated Tests
- `php artisan test --filter=HolidayTest`
  - Test CRUD (create single, create bulk, read, update, delete, delete bulk).
  - Test import Excel sukses.
  - Test import Excel gagal karena duplikat internal (dalam file).
  - Test import Excel gagal karena duplikat terhadap DB → pastikan **rollback** terjadi (0 row inserted).
  - Test download template.
  - Test filter by tahun dan search.

### Manual Verification
- Hit endpoint via Postman/Thunder Client.
- Upload file Excel template yang sudah diisi → cek data masuk di DB.
- Upload file Excel dengan tanggal duplikat → pastikan response error dan DB tidak berubah.
- Pastikan SLACalculator masih bekerja normal setelah ada perubahan data libur.
