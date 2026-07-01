# Rumus Indikator (KPI) Sub Prasojo API

Dokumen ini berisi daftar rumus dan logika kalkulasi yang digunakan untuk menghasilkan nilai pada card indikator (KPI) di berbagai modul aplikasi (Dashboard, SLA, Peringkat Operator, Pengajuan, Wilayah, dan Ulasan).

---

## 1. Modul Dashboard (`DashboardService.php`)

Modul ini menampilkan ringkasan data pengajuan secara keseluruhan.

- **Total Pengajuan:**
  Jumlah seluruh `ajuan_id` yang masuk sesuai dengan filter yang aktif (wilayah, layanan, dsb).
  *Query/Rumus:* `COUNT(ajuan_id)`

- **Total Selesai:**
  Jumlah pengajuan yang memiliki status dalam kelompok Selesai (misalnya: 'Selesai', 'Selesai (Diambil)').
  *Query/Rumus:* `SUM(ajuan_status IN (StatusSelesai))`

- **Total Ditolak:**
  Jumlah pengajuan yang memiliki status dalam kelompok Ditolak.
  *Query/Rumus:* `SUM(ajuan_status IN (StatusDitolak))`

- **Rata-rata Waktu Proses SLA (Menit):**
  Rata-rata selisih waktu (dalam menit) antara waktu pengajuan dibuat (`ajuan_create_datetime`) dan waktu pengajuan diupdate (`ajuan_update_datetime`) khusus untuk pengajuan yang berstatus Selesai.
  *Query/Rumus:* `AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime))`
  *Konversi Jam:* `Menit / 60`

- **Tren Persentase:**
  Membandingkan nilai indikator periode saat ini dengan periode sebelumnya (misal: bulan ini vs bulan lalu).
  *Rumus:* 
  ```
  Jika (Sebelumnya == 0 dan Saat Ini > 0) -> 100%
  Jika (Sebelumnya == 0 dan Saat Ini == 0) -> 0%
  Selain itu -> ((Saat Ini - Sebelumnya) / Sebelumnya) * 100
  ```

---

## 2. Modul SLA (`SLAService.php`)

Modul ini berfokus pada performa penyelesaian layanan (Service Level Agreement) dari seluruh pengajuan berstatus **Selesai**.

- **Total Ajuan (Selesai):**
  Jumlah seluruh `ajuan_id` yang berstatus Selesai.
  *Query/Rumus:* `COUNT(ajuan_id)`

- **Rata-rata Waktu Proses Global (Menit):**
  Sama seperti dashboard, menghitung rata-rata selisih waktu penyelesaian.
  *Query/Rumus:* `AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime))`
  
- **Total Memenuhi SLA:**
  Jumlah pengajuan Selesai yang durasi penyelesaiannya **kurang dari atau sama dengan** Target SLA.
  *Default Target SLA:* 6 Jam (360 Menit)
  *Query/Rumus:* `SUM(Waktu_Proses_Menit <= Target_SLA_Menit)`

- **Capaian SLA (%):**
  Persentase pengajuan yang memenuhi target SLA dari total pengajuan yang telah selesai.
  *Rumus:* `(Total Memenuhi SLA / Total Ajuan) * 100`

- **Rata-rata Waktu Per Layanan:**
  Perhitungan rata-rata menit dikelompokkan (Group By) berdasarkan kode layanan (`ajuan_layanan_kode`).

---

## 3. Modul Peringkat Operator (`PeringkatOperatorService.php`)

Modul ini menghitung performa masing-masing operator berdasarkan log aktivitas perubahan status pengajuan.

- **Total Layanan (yang ditangani Operator):**
  Jumlah seluruh log status (`log_id`) yang diinput atau dikerjakan oleh admin/operator terkait.
  *Query/Rumus:* `COUNT(log_ajuan_status.log_id)`

- **Total Selesai (oleh Operator):**
  Jumlah log status dimana operator mengubah status pengajuan menjadi 'SELESAI'.
  *Query/Rumus:* `SUM(log_ajuan_status.log_status = 'SELESAI')`

- **Tingkat Selesai (%):**
  Persentase layanan yang diselesaikan oleh operator dibanding total layanan yang ia tangani.
  *Rumus:* `(Total Selesai / Total Layanan) * 100`

- **Rata-rata Durasi Pengerjaan (Menit):**
  Rata-rata selisih waktu antara kapan ajuan dibuat oleh pelapor (`ajuan_create_datetime`) dengan kapan operator memprosesnya menjadi selesai (`log_create_datetime`).
  *Query/Rumus:* `AVG(TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, log_ajuan_status.log_create_datetime))`
  *Konversi Jam:* `Menit / 60`

---

## 4. Modul Pengajuan (`PengajuanService.php`)

Modul ini mengelola data lembar kerja dan produk terkait pengajuan.

- **Total Data (Berdasarkan List):**
  Menggunakan query paginate yang secara internal melakukan perhitungan `COUNT` dari seluruh record `lembar_kerja` atau `produk` yang sesuai filter.

- **Distribusi Chart Status (Donut Chart):**
  Menghitung jumlah pengajuan berdasarkan status pada tabel lembar kerja.
  *Query/Rumus:* `COUNT(*)` lalu di-group by `lk_status`

- **Distribusi Chart Layanan (Bar Chart):**
  Menghitung jumlah pengajuan berdasarkan kode layanan pada tabel lembar kerja.
  *Query/Rumus:* `COUNT(lembar_kerja.lk_id)` lalu di-group by `layanan_kode`

---

## 5. Modul Wilayah (`WilayahService.php`)

Modul ini menyajikan distribusi pengajuan berdasarkan wilayah (Kecamatan atau Desa/Kelurahan).

- **Total Ajuan (Per Wilayah):**
  Jumlah total ajuan per kecamatan atau desa.
  *Query/Rumus:* `COUNT(ajuan_id)` di-group by `ajuan_kecamatan_code` atau `ajuan_desa_code`

- **Total Selesai (Per Wilayah):**
  Jumlah ajuan berstatus Selesai per kecamatan atau desa.
  *Query/Rumus:* `SUM(ajuan_status IN (StatusSelesai))`

- **Rata-rata Waktu Penyelesaian (Menit per Wilayah):**
  Rata-rata durasi penyelesaian untuk ajuan berstatus selesai.
  *Query/Rumus:* `AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime))` khusus untuk status Selesai

- **Rasio Selesai (%):**
  Persentase tingkat penyelesaian ajuan untuk wilayah tersebut.
  *Rumus:* `(Total Selesai / Total Ajuan) * 100`
  
---

## 6. Modul Ulasan (`UlasanService.php`)

Modul ini merangkum indeks kepuasan masyarakat (IKM) berdasarkan ulasan pada pengajuan.

- **Rata-rata Bintang:**
  Nilai rata-rata dari seluruh rating ulasan yang diberikan pelapor.
  *Query/Rumus:* `AVG(review_rating)`

- **Distribusi Bintang (5, 4, 3, 2, 1):**
  Menghitung jumlah masing-masing kategori bintang yang masuk (berdasarkan angka rating 1-5).
  *Query/Rumus (Bintang N):* `SUM(CASE WHEN review_rating = N THEN 1 ELSE 0 END)`
