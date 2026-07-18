# Pembahasan Git Backend (Sub Prasojo API)

Berikut adalah ringkasan mengenai pendekatan konfigurasi branching, kolaborasi tim, dan log aktivitas pengerjaan untuk proyek backend API.

## Konfigurasi & Pengaturan Branching

Dalam pengembangan **Sub Prasojo API**, kami menggunakan strategi branching yang berpusat pada branch `staging` sebagai parent branch (environment staging/development utama). Pendekatan yang digunakan adalah sebagai berikut:

### 1. Environment & Parent Branch
*   **Production/Master:** Kode stabil yang sudah siap digunakan di-production (biasanya di branch `main` atau `master`).
*   **Development/Staging:** Branch `staging` bertindak sebagai branch integrasi utama untuk memastikan semua fitur berjalan baik sebelum masuk ke production.

### 2. Feature Branching
Setiap pengerjaan task atau fitur baru tidak dilakukan secara langsung di `staging`. 
*   **Penamaan Branch:** Branch fitur dibuat dari `staging` dengan konvensi penamaan: `feature/<nama-task>`.
*   **Tujuan:** Ini mengisolasi perubahan dan memudahkan review serta proses rollback jika terjadi kesalahan.

### 3. Konvensi Commit (Conventional Commits)
Kami menggunakan Conventional Commits untuk menjaga riwayat git tetap rapi dan deskriptif. Contoh format:
*   `feat: add new endpoint for submission filters` (Fitur baru)
*   `fix: resolve N+1 query issue in Ajuan model` (Perbaikan bug/performa)
*   `chore: update database connection config` (Maintenance, tugas-tugas kecil)
*   `refactor: move business logic to UlasanService` (Refactoring kode)

### 4. Siklus Integrasi & Kolaborasi (Pull Request)
*   **Sinkronisasi Awal:** Selalu jalankan `git fetch origin` dan `git pull origin staging` untuk mencegah konflik dengan perubahan terbaru dari remote.
*   **Pembuatan PR:** Setelah task selesai di branch fitur, kami membuat Pull Request (PR) ke branch `staging`.
*   **Merge & Deploy:** Setelah PR direview dan aman, perubahan di-merge ke `staging`.


---

## Log Aktivitas & Riwayat Pengerjaan (Pull/Push)

Di bawah ini adalah rekam jejak fase pengerjaan yang telah dilalui:

### Fase 1: Inisialisasi & Setup Dasar
*   **Pull:** Menarik kerangka awal proyek Laravel dari repositori utama.
*   **Commit (`feat: setup laravel project & basic config`):** Konfigurasi `.env` dan struktur awal proyek.
*   **Commit (`feat: configure dual database connection`):** Setup koneksi database (Dual Connection). Konfigurasi `mysql` untuk dashboard (Read-Write) dan `mysql_prasojo` untuk operasional lama (Read-Only).

### Fase 2: Implementasi Model & Skema Database
*   **Commit (`feat: implement Ajuan model with dual connection`):** Pembuatan model `Ajuan` (`app/Models/Prasojo/Ajuan.php`) yang terhubung ke koneksi `mysql_prasojo`. Pengaturan `$timestamps = false` dan penyesuaian primary key karena perbedaan struktur tabel bawaan.
*   **Commit (`chore: add necessary database indexes`):** Penambahan index via migration pada database dashboard untuk tabel `sub_users` dan pembuatan skrip index optimasi pada tabel operasional (seperti `ajuan_status`, `ajuan_create_datetime`) sesuai standar.

### Fase 3: Pembuatan Service Layer & Filter Logic
*   **Commit (`feat: create SLAFilter logic`):** Implementasi logic pemfilteran berdasarkan Service Level Agreement di `SLAFilter`.
*   **Commit (`feat: implement UlasanService business logic`):** Ekstraksi logika bisnis kompleks ke `UlasanService` agar Controller tetap *thin* dan mudah di-*test*.
*   **Commit (`feat: setup API FilterController`):** Implementasi `FilterController` (`app/Http/Controllers/Api/V1/FilterController.php`) sebagai pintu masuk request dari frontend (menggunakan format tanggal `d-m-Y`).

### Fase 4: Optimasi, Testing, & Keamanan Sistem
*   **Commit (`fix: prevent N+1 query issue on Ajuan resource`):** Mengimplementasikan Eager Loading (`with()`) pada pemanggilan data relasional (misalnya relasi pelapor pada tabel Ajuan) agar tidak memberatkan server.
*   **Commit (`test: create TestApi command`):** Penambahan `TestApi` di `app/Console/Commands/TestApi.php` untuk memfasilitasi pengujian internal performa endpoint dan format response.
*   **Push & PR:** Push branch `feature/setup-backend-core` ke remote, membuat PR ke `staging`, dan melakukan Merge setelah *automated tests* berhasil dilewati.
