# Product Requirements Document (PRD) - Backend API
**Proyek:** Sistem Monitoring Layanan Disdukcapil (Dashboard Eksekutif)
**Status:** Final Draft
**Target Audiens:** Tim Backend Developer

---

## 1. Pendahuluan & Objektif
Sistem ini merupakan aplikasi *Dashboard Executive* dan *Monitoring* yang bersifat **Read-Only** untuk data layanan kependudukan. Sistem ini bertugas membaca data operasional (dari skema `prasojo_nodata.sql`) dan menyajikannya menjadi visualisasi metrik kinerja, laporan wilayah, dan pemantauan SLA.

Tidak ada transaksi pengubahan data layanan (Create/Update/Delete pengajuan) yang terjadi pada sistem ini. Seluruh modifikasi data (*write operations*) hanya berlaku pada tabel *User Management* khusus untuk akses dashboard ini.

---

## 2. Arsitektur Kode & Desain Pattern
Sistem backend **WAJIB** menerapkan **Service Pattern** untuk menjaga kode tetap rapi (*clean architecture*).
*   **Controllers:** Menerima *Request* dan mengembalikan *Response*.
*   **Requests:** Memvalidasi *payload* input dari *client* (Form Request Validation).
*   **Services:** Berisi inti logika bisnis (pengolahan data, agregasi).
*   **Filters:** Mengisolasi dan memproses logika penyaringan sebelum di-*query*.
*   **Enums:** Mendefinisikan konstanta baku (seperti Status Ajuan: `MENUNGGU`, `SELESAI`, `DIPROSES`).
*   **JsonResponse:** Mengonstruksi struktur JSON baku.

---

## 3. Standarisasi Response & Penanganan Error
Semua pesan keluaran API (*message*) **MUTLAK menggunakan Bahasa Indonesia**.

### Format Sukses (2xx)
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data",
  "data": [ ... ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 145,
    "total_page": 15
  }
}
```
*(Catatan: Field `meta` hanya wajib disertakan pada endpoint yang memiliki paginasi. Untuk endpoint tunggal seperti get profile, field `meta` bisa dihilangkan dan `data` dapat berupa Object tunggal `{}`).*

### Format Error Handling (4xx & 5xx)
```json
{
  "status": false,
  "code": 404,
  "message": "Data tidak ditemukan atau kecamatan tidak valid",
  "data": null
}
```

---

## 4. Arsitektur Data & Autentikasi

### 4.1. Tabel User Khusus (Dashboard Users)
Backend WAJIB membuat tabel baru (misal: `monitoring_users`).
*   **Kolom minimal:** `id`, `email`, `password` (hashed), `created_at`, `updated_at`.
*   *(Catatan: Tidak ada field `role`).*

### 4.2. Autentikasi (PASETO)
*   Sistem menggunakan **PASETO (Platform-Agnostic Security Tokens)**.
*   `POST /api/auth/login` (email & password)
*   `POST /api/auth/refresh` (refresh token)
*   `GET /api/auth/me` (get profile)

### 4.3. Pemetaan Status Layanan (Enums)
Backend **WAJIB** membuatkan *Class Enum* khusus agar tidak ada *hardcode string* yang tersebar di *controller/service*. Definisi string merujuk pada format lama aplikasi:

**Contoh Penerapan di Backend (PHP/Laravel):**
```php
namespace App\Enums;

enum AjuanStatus: string {
    // Alur Lembar Kerja
    case DIAJUKAN = 'Diajukan';
    case BELUM_DIVERIFIKASI = 'Belum Diverifikasi';
    case DIVERIFIKASI = 'Diverifikasi';
    case DIPROSES = 'Diproses';
    case MENUNGGU_KONFIRMASI = 'Menunggu Konfirmasi';
    case DISETUJUI = 'Disetujui';
    case DITOLAK = 'Ditolak';
    case SELESAI = 'Selesai';

    // Alur Produk
    case DIAJUKAN_TTE = 'Diajukan TTE';
    case TIDAK_DIPROSES = 'Tidak Diproses';
    case SIAP_DOWNLOAD = 'Siap Didownload';
    case SIAP_DICETAK = 'Siap Dicetak';
    case SUDAH_DICETAK = 'Sudah Dicetak';
    case SIAP_DIAMBIL = 'Siap Diambil';

    // Helper method untuk query Dashboard
    public static function getStatusSelesai(): array {
        return [self::SELESAI->value, self::SIAP_DOWNLOAD->value, self::SIAP_DIAMBIL->value, self::SUDAH_DICETAK->value];
    }

    public static function getStatusMenunggu(): array {
        return [self::DIAJUKAN->value, self::BELUM_DIVERIFIKASI->value, self::DIPROSES->value, self::MENUNGGU_KONFIRMASI->value, self::DIAJUKAN_TTE->value, self::SIAP_DICETAK->value];
    }
}
```

**Penerapan pada Query KPI Dashboard:**
Saat *Backend* menghitung "Total Selesai" dan "Total Menunggu" untuk API `/api/dashboard/kpi`, cukup panggil *helper Enum* di atas:
```php
// Menghitung Ajuan Selesai
$totalSelesai = Ajuan::whereIn('ajuan_status', AjuanStatus::getStatusSelesai())->count();

// Menghitung Ajuan Berjalan (Menunggu)
$totalMenunggu = Ajuan::whereIn('ajuan_status', AjuanStatus::getStatusMenunggu())->count();
```

---

## 5. Kesesuaian Database & Mapping Kolom (`prasojo_nodata.sql`)
Berdasarkan analisa tabel pada database `prasojo_nodata.sql`, berikut adalah sumber kolom yang digunakan:
*   **Total Ajuan:** Hitung (*Count*) dari `ajuan.ajuan_id`.
*   **Durasi & Kecepatan:** Dihitung dari selisih `ajuan_update_datetime` dikurangi `ajuan_create_datetime`.
*   **Kinerja Operator:** Relasi `ajuan.ajuan_pelapor_id` = `admin.id`. Nama pegawai dari `admin.fullname`.
*   **Distribusi Wilayah:** Agregasi dari kolom `ajuan_kecamatan_code` dan `ajuan_kecamatan_name`.
*   **Monitoring Ulasan:** `ajuan_review` (`review_rating` dan `review_content`). Filter layanan didapat dari *JOIN* `ajuan_review.review_ajuan_id` dengan `ajuan.ajuan_id` untuk mendapatkan `ajuan_layanan_kode`.
*   **Jalur Pelaporan (Pelapor):** Pada tabel `ajuan`, terdapat kolom `ajuan_is_online` (0=offline, 1=online), `ajuan_is_mandiri` (0=multi ajuan/operator, 1=sendiri/mandiri), serta `ajuan_pelapor_role_name` (nama level pelapor). Kolom-kolom ini yang akan dikombinasikan Backend untuk memfilter "Jalur Pelaporan/Pelapor".

---

## 6. Parameter Filter (Global, Sorting & Pencarian)

Paginasi di-handle otomatis dengan *Pagination* bawaan framework Backend. Format tanggal adalah **`dd-mm-yyyy`**.

### 6.1. Filter Global & Umum
Seluruh *endpoint list/table* (Kecuali Dashboard) wajib mendukung parameter berikut:
1.  **Periode & Tahun:** `?periode_bulan=1..12` (Otomatis menggunakan tahun saat ini).
2.  **Rentang Kustom:** `?start_date=dd-mm-yyyy & end_date=dd-mm-yyyy`. *(Jika dikirim, filter periode_bulan diabaikan).*
3.  **Pencarian:** `?search=` (Keyword pencarian untuk nama, NIK, dsb).
4.  **Sortir:** `?sort_by=` (kolom) & `?sort_dir=asc|desc` (Urutkan terbaru/lama, atau abjad A-Z/Z-A).

### 6.2. Filter Spesifik per Halaman/Modul
Setiap endpoint API di bawah memiliki parameter spesifik opsional tambahan:

*   **Dashboard:** `?id_kecamatan=` & `?id_layanan=`
*   **Pengajuan (Ajuan/Lembar Kerja/Produk):** `?id_kecamatan=`, `?id_layanan=`, `?status=`, `?pelapor=`, `?search_no_reg=`
*   **Monitoring Operator:** `?id_kecamatan=`, `?search_nama=` (Tanpa filter layanan).
*   **Monitoring Wilayah:** `?id_kecamatan=` (Tanpa filter layanan).
*   **SLA Monitoring:** `?id_kecamatan=`
*   **Monitoring Ulasan:** `?rating=`, `?id_layanan=`

---

## 7. Spesifikasi Lengkap API (Endpoints)

### A. Modul Auth & Profile
*   `POST /api/auth/login` → Validasi `email` & `password`, me-return PASETO token.
*   `POST /api/auth/refresh` → Mengembalikan PASETO *Access Token* yang baru.
*   `GET /api/auth/me` → Me-return profile *user* yang sedang login.
*   `PUT /api/auth/profile` → Update data profile.
*   `POST /api/auth/logout` → *Blacklist token* / hancurkan sesi.

### B. Modul Pengajuan (Lembar Kerja, Semua Ajuan, Produk)
*Dibuat menjadi 1 endpoint master (reusable).*
*   `GET /api/pengajuan` 
    *   **Deskripsi:** Mengambil daftar pengajuan (*Pagination* aktif).
    *   **Query Params Khusus Endpoint Ini:** `?status_kategori=lembar_kerja|produk|all` (Dipetakan ke Class Enum).
*   `GET /api/pengajuan/export` → Export tabel ke **Excel (.xlsx)**.

### C. Modul Dashboard
*   `GET /api/dashboard/kpi` → 4 Card (Total Pengajuan, Selesai, Ditolak, Rata-rata Durasi SLA).
    *   *Mapping Database:* Bersumber dari tabel `ajuan`.
    *   **Total Pengajuan:** `COUNT(ajuan_id)`
    *   **Selesai:** `COUNT(ajuan_id)` di mana `ajuan_status` = 'Selesai' / 'Siap Diambil' / 'Sudah Dicetak'.
    *   **Ditolak:** `COUNT(ajuan_id)` di mana `ajuan_status` = 'Ditolak' / 'Tidak Diproses'.
    *   **Rata-rata Durasi SLA:** `AVG(ajuan_update_datetime - ajuan_create_datetime)` untuk ajuan yang sudah selesai.
    *   **Indikator Tren (Persentase):** Menghitung nilai tren setiap card dibandingkan periode sebelumnya. **Rumus:** `((Nilai Saat Ini - Nilai Sebelumnya) / Nilai Sebelumnya) * 100`. (Kondisi khusus: Jika sebelumnya 0 dan saat ini > 0, hasil 100%. Jika keduanya 0, hasil 0%).
*   `GET /api/dashboard/chart-trend` → Array data *Line Chart* per hari.
*   `GET /api/dashboard/top-wilayah` → Array data *Bar Chart* (Top 5 Kecamatan).

### D. Modul Monitoring Operator
*   `GET /api/operator/kpi-global` → 3 Card (Total Aktif, Total Berkas Dikerjakan, Rata-rata Kecepatan).
*   `GET /api/operator/ranking` → Daftar urutan operator (*Pagination* aktif).
*   `GET /api/operator/{id_operator}/detail` → Rapor spesifik operator & Tabel riwayat kerja.
*   `GET /api/operator/export` → Export tabel ranking **Excel (.xlsx)**.

### E. Modul Monitoring Wilayah
*   `GET /api/wilayah/distribusi` → Daftar volume pengajuan dikelompokkan per `id_kecamatan` (*Pagination* aktif).
*   `GET /api/wilayah/export` → Export tabel distribusi wilayah **Excel (.xlsx)**.

### F. Modul SLA Monitoring
*(Target waktu per layanan di-hardcode dalam Enums/Config Array di Backend Code)*
*   `GET /api/sla/kpi` → 2 Card (Rata-rata Waktu Proses Global, Persentase Pencapaian SLA).
*   `GET /api/sla/layanan` → Tabel komparasi waktu SLA per Layanan (*Pagination* aktif).
*   `GET /api/sla/export` → Export tabel SLA **Excel (.xlsx)**.

### G. Modul Monitoring Ulasan
*   `GET /api/ulasan/kpi` → *Hero Score* Rata-rata bintang dan Array Hitungan untuk *Donut Chart*.
*   `GET /api/ulasan/list` → Tabel komentar warga (*Pagination* aktif).
*   `GET /api/ulasan/export` → Export tabel ulasan **Excel (.xlsx)**.

---

## 8. Performance & Indexing
1.  **Database Indexing:** Tambahkan Index pada MySQL di kolom: `ajuan_status`, `ajuan_create_datetime`, `ajuan_update_datetime`, `ajuan_no_reg`, `ajuan_kecamatan_code`, `ajuan_is_online`, `ajuan_pelapor_role_name`, dan `ajuan_pelapor_id`.
2.  **Caching:** Terapkan strategi *caching* (Redis) di layer Service untuk komputasi KPI Dashboard.
