---
name: add-feature
description: Workflow standar untuk menambahkan fitur baru berdasarkan dokumentasi di docs/
---

# Workflow: Add Feature

Workflow ini dijalankan saat pengguna meminta agen untuk menambahkan atau mengimplementasikan sebuah fitur baru.

## Langkah-langkah Eksekusi

### 1. Persiapan & Sinkronisasi
- **Baca Dokumentasi:** Periksa `docs/PRD.md`, `docs/api_documentation.md`, dan `docs/database_schema.md` untuk memahami spesifikasi fitur, request/response, dan struktur tabel yang akan digunakan.
- **Buat Branch Baru:** Jalankan prosedur dari `git-branching.md`. Checkout ke branch baru dari `staging-amru` (misalnya: `git checkout -b feature/<nama-fitur> staging-amru`).
- **Pembaruan Plan:** Pastikan fitur yang akan dikerjakan sudah tercatat di `.agents/implementation_plan.md` dan `.agents/task.md`. Jika belum, diskusikan/buat perencanaannya terlebih dahulu.

### 2. Implementasi Kode
- **Tulis Kode:** Buat/ubah Controller, Service, Filter, Model, atau komponen lain sesuai dengan aturan arsitektur di `.agents/rules/architecture.md`.
- **Response Standar:** Gunakan helper class `ApiResponse` (lihat `.agents/rules/api-response.md`) dengan pesan berbahasa Indonesia.
- **Database:** Jika fitur melibatkan pembacaan data operasional, pastikan menggunakan koneksi `mysql_prasojo` (Read-Only).

### 3. Pengujian (Testing)
- **Tulis Test:** Buat Feature Test atau Unit Test di direktori `tests/Feature/` atau `tests/Unit/`.
- **Jalankan Test:** Jalankan `php artisan test` untuk memastikan fitur berjalan sesuai ekspektasi dan tidak merusak fitur lain.

### 4. Penyelesaian (Wrap Up)
- **Log Eksekusi:** Catat ringkasan apa saja yang telah dilakukan beserta timestamp ke dalam direktori `.agents/logs/`.
- **Update Checklist:** Tandai task yang sudah selesai dengan `[x]` pada `.agents/task.md`.
- **Commit:** Lakukan commit menggunakan format Conventional Commits.
- **Merge/PR:** Laporkan kepada pengguna bahwa branch fitur sudah siap untuk di-merge atau dibuatkan Pull Request.
