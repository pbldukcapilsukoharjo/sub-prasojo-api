# Tasks: Modul CRUD Master Hari Libur Nasional + Import Excel

## Fase 0 — Persiapan Git Branch
- [x] 0.1 Checkout ke `staging-amru` dan pull terbaru
  - `git checkout staging-amru`
  - `git pull origin staging-amru`
- [x] 0.2 Buat branch fitur baru
  - `git checkout -b feature/crud-master-hari-libur-byamru`

## Fase 1 — Foundation (Service + Request Validation)
- [x] 1.1 Buat `app/Services/HolidayService.php`
  - [x] Method `index(array $filters)` — query + filter tahun + search + paginasi
  - [x] Method `store(array $data)` — bulk create dalam `DB::transaction`, cek duplikat tanggal vs DB
  - [x] Method `show(int $id)` — findOrFail
  - [x] Method `update(int $id, array $data)` — update single, cek unique tanggal (exclude self)
  - [x] Method `destroy(int $id)` — delete single
  - [x] Method `destroyBulk(array $ids)` — delete multiple by ids
- [x] 1.2 Buat Form Request classes
  - [x] `app/Http/Requests/Holiday/IndexHolidayRequest.php` (filter: tahun, search, page, per_page)
  - [x] `app/Http/Requests/Holiday/StoreHolidayRequest.php` (array holidays.*.tanggal + keterangan, cek distinct)
  - [x] `app/Http/Requests/Holiday/UpdateHolidayRequest.php` (tanggal unique exclude self, keterangan)
  - [x] `app/Http/Requests/Holiday/ImportHolidayRequest.php` (file mimes:xlsx,xls max:2048)
  - [x] `app/Http/Requests/Holiday/DestroyBulkHolidayRequest.php` (ids array, exists check)
- [x] 1.3 Commit fase 1
  - `git add .`
  - `git commit -m "feat: tambah service dan validasi request modul hari libur"`

## Fase 2 — Import & Template Excel (Maatwebsite)
- [x] 2.1 Buat `app/Imports/HolidayImport.php`
  - [x] Implement `ToCollection` untuk parsing manual
  - [x] Validasi per baris: tanggal valid (Y-m-d), keterangan tidak kosong
  - [x] Kumpulkan error rows, return parsed Collection ke service
- [x] 2.2 Buat `app/Exports/HolidayTemplateExport.php`
  - [x] Header: Tanggal (YYYY-MM-DD), Keterangan
  - [x] 2 contoh baris data
  - [x] Auto-size, bold header, format kolom tanggal sebagai teks
- [x] 2.3 Tambahkan method di `HolidayService`
  - [x] `importFromExcel(UploadedFile $file)` — parse → cek duplikat internal → cek duplikat DB → transaction insert atau rollback
  - [x] `generateTemplate()` — return Excel download menggunakan `HolidayTemplateExport`
- [x] 2.4 Commit fase 2
  - `git add .`
  - `git commit -m "feat: tambah import dan template excel hari libur"`

## Fase 3 — Controller & Route
- [x] 3.1 Buat `app/Http/Controllers/Api/V1/HolidayController.php`
  - [x] Dependency injection `HolidayService`
  - [x] Method `index(IndexHolidayRequest)` → list + paginasi
  - [x] Method `store(StoreHolidayRequest)` → bulk create
  - [x] Method `show(int $id)` → detail
  - [x] Method `update(UpdateHolidayRequest, int $id)` → update single
  - [x] Method `destroy(int $id)` → delete single
  - [x] Method `destroyBulk(DestroyBulkHolidayRequest)` → delete multiple
  - [x] Method `import(ImportHolidayRequest)` → import Excel
  - [x] Method `template()` → download template
  - [x] Semua method: try-catch + Log::error + ApiResponse
- [x] 3.2 Registrasi route di `routes/api.php`
  - [x] Route group `holidays` dengan middleware `paseto.auth`
  - [x] Urutan route: `/template`, `/import`, `/bulk` sebelum `/{id}`
- [x] 3.3 Commit fase 3
  - `git add .`
  - `git commit -m "feat: tambah controller dan route API holidays"`

## Fase 4 — Testing
- [x] 4.1 Buat `tests/Feature/Holiday/HolidayTest.php`
  - [x] Test list holidays (GET /) — 200 OK + paginasi
  - [x] Test filter by tahun
  - [x] Test search by keterangan
  - [x] Test create single holiday (POST /)
  - [x] Test create bulk holidays (POST /)
  - [x] Test create gagal karena tanggal duplikat di DB
  - [x] Test create gagal karena tanggal duplikat di dalam request body
  - [x] Test show detail (GET /{id})
  - [x] Test update (PUT /{id})
  - [x] Test update gagal karena tanggal duplikat
  - [x] Test delete single (DELETE /{id})
  - [x] Test delete bulk (DELETE /bulk)
  - [x] Test download template (GET /template) — assert header content-type xlsx
  - [x] Test import Excel sukses
  - [x] Test import Excel gagal — duplikat internal dalam file
  - [x] Test import Excel gagal — duplikat terhadap data DB existing → assert rollback (0 rows added)
  - [x] Test import Excel gagal — format file salah (bukan xlsx)
- [x] 4.2 Jalankan `php artisan test --filter=HolidayTest` → pastikan semua hijau
- [x] 4.3 Commit fase 4
  - `git add .`
  - `git commit -m "test: tambah pengujian fitur CRUD dan import hari libur"`

## Fase 5 — Dokumentasi & Finalisasi
- [x] 5.1 Update `docs/api_documentation.md`
  - [x] Tambahkan section "8. Modul Master Hari Libur" dengan spec lengkap semua endpoint
- [x] 5.2 Update `docs/api_endpoints_sla.md`
  - [x] Tambahkan referensi endpoint holidays
- [x] 5.3 Review akhir
  - [x] Pastikan SLACalculator.php masih bekerja normal (tidak ada breaking change)
  - [x] Pastikan tidak ada N+1 query
  - [x] Jalankan `php artisan test` full suite
- [x] 5.4 Commit fase 5
  - `git add .`
  - `git commit -m "docs: tambah dokumentasi endpoint master hari libur"`

## Fase 6 — Push, Pull Request & Merge
- [x] 6.1 Push branch fitur ke remote
  - `git push -u origin feature/crud-master-hari-libur-byamru`
- [x] 6.2 Buat Pull Request → `staging-amru`
  - Push branch fitur & persiapkan PR ke `staging-amru`
- [x] 6.3 Merge PR ke `staging-amru`
  - Merge branch `feature/crud-master-hari-libur-byamru` ke `staging-amru` dan push remote
- [x] 6.4 Buat Pull Request `staging-amru` → `staging`
  - Sediakan link PR `staging-amru` → `staging` untuk integrasi
