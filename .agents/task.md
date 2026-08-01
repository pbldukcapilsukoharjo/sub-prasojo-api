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
- [ ] 3.1 Buat `app/Http/Controllers/Api/V1/HolidayController.php`
  - [ ] Dependency injection `HolidayService`
  - [ ] Method `index(IndexHolidayRequest)` → list + paginasi
  - [ ] Method `store(StoreHolidayRequest)` → bulk create
  - [ ] Method `show(int $id)` → detail
  - [ ] Method `update(UpdateHolidayRequest, int $id)` → update single
  - [ ] Method `destroy(int $id)` → delete single
  - [ ] Method `destroyBulk(DestroyBulkHolidayRequest)` → delete multiple
  - [ ] Method `import(ImportHolidayRequest)` → import Excel
  - [ ] Method `template()` → download template
  - [ ] Semua method: try-catch + Log::error + ApiResponse
- [ ] 3.2 Registrasi route di `routes/api.php`
  - [ ] Route group `holidays` dengan middleware `paseto.auth`
  - [ ] Urutan route: `/template`, `/import`, `/bulk` sebelum `/{id}`
- [ ] 3.3 Commit fase 3
  - `git add .`
  - `git commit -m "feat: tambah controller dan route API holidays"`

## Fase 4 — Testing
- [ ] 4.1 Buat `tests/Feature/Holiday/HolidayTest.php`
  - [ ] Test list holidays (GET /) — 200 OK + paginasi
  - [ ] Test filter by tahun
  - [ ] Test search by keterangan
  - [ ] Test create single holiday (POST /)
  - [ ] Test create bulk holidays (POST /)
  - [ ] Test create gagal karena tanggal duplikat di DB
  - [ ] Test create gagal karena tanggal duplikat di dalam request body
  - [ ] Test show detail (GET /{id})
  - [ ] Test update (PUT /{id})
  - [ ] Test update gagal karena tanggal duplikat
  - [ ] Test delete single (DELETE /{id})
  - [ ] Test delete bulk (DELETE /bulk)
  - [ ] Test download template (GET /template) — assert header content-type xlsx
  - [ ] Test import Excel sukses
  - [ ] Test import Excel gagal — duplikat internal dalam file
  - [ ] Test import Excel gagal — duplikat terhadap data DB existing → assert rollback (0 rows added)
  - [ ] Test import Excel gagal — format file salah (bukan xlsx)
- [ ] 4.2 Jalankan `php artisan test --filter=HolidayTest` → pastikan semua hijau
- [ ] 4.3 Commit fase 4
  - `git add .`
  - `git commit -m "test: tambah pengujian fitur CRUD dan import hari libur"`

## Fase 5 — Dokumentasi & Finalisasi
- [ ] 5.1 Update `docs/api_documentation.md`
  - [ ] Tambahkan section "8. Modul Master Hari Libur" dengan spec lengkap semua endpoint
- [ ] 5.2 Update `docs/api_endpoints_sla.md`
  - [ ] Tambahkan referensi endpoint holidays
- [ ] 5.3 Review akhir
  - [ ] Pastikan SLACalculator.php masih bekerja normal (tidak ada breaking change)
  - [ ] Pastikan tidak ada N+1 query
  - [ ] Jalankan `php artisan test` full suite
- [ ] 5.4 Commit fase 5
  - `git add .`
  - `git commit -m "docs: tambah dokumentasi endpoint master hari libur"`

## Fase 6 — Push, Pull Request & Merge
- [ ] 6.1 Push branch fitur ke remote
  - `git push -u origin feature/crud-master-hari-libur-byamru`
- [ ] 6.2 Buat Pull Request → `staging-amru`
  - `gh pr create --base staging-amru --head feature/crud-master-hari-libur-byamru --title "feat: implementasi modul CRUD master hari libur nasional + import excel" --body "..."`
  - Deskripsi PR (bahasa Indonesia):
    ```
    ## Ringkasan
    Implementasi modul CRUD Master Hari Libur Nasional dengan fitur:
    - Input hari libur satuan dan massal (bulk)
    - Import hari libur melalui file Excel (.xlsx)
    - Download template Excel untuk panduan pengisian
    - Rollback otomatis jika ditemukan data duplikat saat import
    - Hapus hari libur satuan dan massal (bulk delete)
    - Filter berdasarkan tahun dan pencarian keterangan

    ## File yang Ditambahkan
    - `app/Services/HolidayService.php`
    - `app/Http/Controllers/Api/V1/HolidayController.php`
    - `app/Http/Requests/Holiday/*.php` (5 request classes)
    - `app/Imports/HolidayImport.php`
    - `app/Exports/HolidayTemplateExport.php`
    - `tests/Feature/Holiday/HolidayTest.php`

    ## File yang Dimodifikasi
    - `routes/api.php` — tambah route group holidays
    - `docs/api_documentation.md` — tambah section endpoint hari libur
    ```
- [ ] 6.3 Merge PR ke `staging-amru`
  - `gh pr merge --squash` atau merge via GitHub web
- [ ] 6.4 Buat Pull Request `staging-amru` → `staging`
  - `gh pr create --base staging --head staging-amru --title "feat: modul master hari libur nasional (CRUD + import excel)" --body "Merge modul master hari libur nasional dari staging-amru ke staging untuk integrasi."`
