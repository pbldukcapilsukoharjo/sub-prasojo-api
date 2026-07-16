# Implementasi Penghitungan SLA Berbasis Jam Kerja

Pembaruan mekanisme penghitungan Service Level Agreement (SLA) untuk menghitung durasi layanan (dalam menit) berdasarkan jam operasional Dukcapil dan mengabaikan hari libur nasional serta *weekend*.

## User Review Required

> [!IMPORTANT]
> Harap tinjau rencana ini. Penghitungan SLA akan ditarik dari *query realtime* di `SLAService.php` menjadi perhitungan *background* (*Cron Job*) yang disimpan pada tabel agregasi `ajuan_sla_summary`.

## Proposed Changes

### Database Migrations

#### [NEW] [create_master_libur_nasional_table.php](file:///c:/projects/laravel/sub-prasojo-api/database/migrations)
- Membuat tabel `master_libur_nasional` di database `mysql` (default) untuk menyimpan data tanggal merah tambahan (Cuti Bersama, dsb).
- Kolom: `id`, `tanggal` (date, unique), `keterangan` (string).

#### [NEW] [create_ajuan_sla_summary_table.php](file:///c:/projects/laravel/sub-prasojo-api/database/migrations)
- Membuat tabel `ajuan_sla_summary` di database `mysql`.
- Kolom: `ajuan_id` (integer, PK/Unique), `waktu_mulai` (datetime), `waktu_selesai` (datetime), `durasi_sla_menit` (integer).

### Eloquent Models

#### [NEW] [MasterLiburNasional.php](file:///c:/projects/laravel/sub-prasojo-api/app/Models/MasterLiburNasional.php)
- Model untuk tabel `master_libur_nasional`.

#### [NEW] [AjuanSlaSummary.php](file:///c:/projects/laravel/sub-prasojo-api/app/Models/AjuanSlaSummary.php)
- Model untuk tabel `ajuan_sla_summary`.

### Dependencies & Setup

- Instalasi library `spatie/holidays` menggunakan composer untuk mendapatkan data libur nasional paten Indonesia.

### Console Command / Cron Job

#### [NEW] [CalculateSLACommand.php](file:///c:/projects/laravel/sub-prasojo-api/app/Console/Commands/CalculateSLACommand.php)
- Membuat command Artisan `app:calculate-sla`.
- **Logic:**
  1. Ambil semua `ajuan_id` dari tabel `log_ajuan_status` yang berstatus `'SELESAI DIPROSES'`.
  2. Filter `ajuan_id` yang belum ada di tabel `ajuan_sla_summary` (atau update jika ada perubahan).
  3. Untuk setiap ajuan, cari log terbaru `Start Time` (`'PROSES VERIFIKASI'`) dan log terbaru `End Time` (`'SELESAI DIPROSES'`).
  4. Hitung selisih waktu dalam menit, menggunakan library Carbon dan logika *Business Hours* (Senin-Kamis 08:00-15:00, Jumat 08:00-13:00) yang melewati pengecekan hari libur dari `spatie/holidays` & tabel `master_libur_nasional`.
  5. Simpan hasil kalkulasi menit ke `ajuan_sla_summary`.

#### [MODIFY] [Kernel.php](file:///c:/projects/laravel/sub-prasojo-api/app/Console/Kernel.php)
- Mendaftarkan command `app:calculate-sla` agar berjalan otomatis setiap jam/harian.

### Service Updates

#### [MODIFY] [SLAService.php](file:///c:/projects/laravel/sub-prasojo-api/app/Services/SLAService.php)
- Mengubah fungsi laporan/KPI (seperti fungsi perolehan waktu rata-rata) agar membaca nilai `durasi_sla_menit` dari tabel agregasi `ajuan_sla_summary` (koneksi `mysql`).
- Menghapus raw query SQL `TIMESTAMPDIFF` kompleks yang berpotensi membebani `mysql_prasojo`.

## Verification Plan

### Automated Tests
- Menambahkan test case / pengujian mandiri sederhana untuk helper penghitung selisih menit guna memastikan logika loncat hari libur dan beda jam (Jumat vs Senin-Kamis) sudah 100% akurat.

### Manual Verification
- Menjalankan migrasi database.
- Menjalankan `php artisan app:calculate-sla` secara manual untuk memverifikasi data berhasil terisi di `ajuan_sla_summary`.
- Membuka halaman pelaporan SLA/KPI di aplikasi dan memastikan performa akses sangat cepat dan durasinya masuk akal.
