# Dokumentasi Database Prasojo
**Database**: `sukoharjokab_prasojo`  
**Server**: MariaDB 10.4.32  
**Generated**: Mar 31, 2026  

---

## Daftar Tabel

| No | Nama Tabel | Deskripsi |
|---|---|---|
| 1 | `admin` | Data pengguna administrator sistem |
| 2 | `admin_auth` | Authentication token untuk admin |
| 3 | `admin_role` | Role/permission untuk admin |
| 4 | `ajuan` | Master data ajuan/permohonan |
| 5 | `ajuan_akta_kelahiran` | Detail ajuan akta kelahiran |
| 6 | `ajuan_akta_kematian` | Detail ajuan akta kematian |
| 7 | `ajuan_datang` | Detail ajuan surat datang/pindah masuk |
| 8 | `ajuan_kia` | Detail ajuan KIA (Kartu Identitas Anak) |
| 9 | `ajuan_kk` | Detail ajuan KK (Kartu Keluarga) |
| 10 | `ajuan_ktpel` | Detail ajuan KTP EL (KTP Elektronik) |
| 11 | `ajuan_pindah` | Detail ajuan surat pindah |
| 12 | `ajuan_rekam_jemput` | Detail ajuan rekam jemput |
| 13 | `ajuan_review` | Review/rating untuk ajuan |
| 14 | `ajuan_update_data` | Detail ajuan update/perubahan data |
| 15 | `announcement` | Pengumuman untuk user dan admin |
| 16 | `bpp` | Berita Pindah Pasir (burial permit) |
| 17 | `bpp_tempat_pemakaman` | Master lokasi tempat pemakaman |
| 18 | `bpp_tempat_pemakaman_jenis` | Master jenis tempat pemakaman |
| 19 | `category` | Kategori untuk post/blog dan report |
| 20 | `config` | Konfigurasi sistem |
| 21 | `delivery` | Data pengiriman produk |
| 22 | `delivery_item` | Item detail dalam pengiriman |
| 23 | `delivery_proses` | Status proses pengiriman |
| 24 | `ilokasi_desa` | Master lokasi desa |
| 25 | `ilokasi_kabupaten` | Master lokasi kabupaten |
| 26 | `ilokasi_kecamatan` | Master lokasi kecamatan |
| 27 | `ilokasi_provinsi` | Master lokasi provinsi |
| 28 | `jenis_ajuan` | Master jenis-jenis ajuan |
| 29 | `layanan` | Master data layanan/service |
| 30 | `layanan_content` | Konten detail untuk layanan |
| 31 | `lembar_kerja` | Lembar kerja/worksheet untuk ajuan |
| 32 | `log_ajuan_status` | Log perubahan status ajuan |
| 33 | `log_produk_status` | Log perubahan status produk |
| 34 | `master_data_dukung` | Master data dokumen dukung |
| 35 | `migrations` | Laravel migration history |
| 36 | `notification` | Notifikasi untuk user |
| 37 | `post` | Konten post/artikel blog |
| 38 | `produk` | Data produk hasil dari ajuan |
| 39 | `site` | Halaman site statis |
| 40 | `user` | Data pengguna regular (citizen) |
| 41 | `user_auth` | Authentication token untuk user |
| 42 | `user_register_verify_data` | Data verifikasi saat registrasi |

---

## Detail Tabel

### 1. admin
**Deskripsi**: Tabel penyimpanan data administrator/operator sistem

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| id | bigint(20) UNSIGNED | NO | - | Primary Key |
| username | varchar(100) | YES | NULL | Username login |
| fullname | varchar(100) | YES | NULL | Nama lengkap |
| nik | varchar(16) | YES | NULL | Nomor Identitas |
| kk | varchar(16) | YES | NULL | Nomor Kartu Keluarga |
| email | varchar(200) | YES | NULL | Email address |
| phone | varchar(13) | YES | NULL | Nomor telepon |
| password | varchar(255) | YES | NULL | Password hash |
| image | longtext | YES | NULL | Foto profil |
| level | varchar(30) | NO | 'operator' | Level: administrator, admin, operator |
| role_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin_role |
| is_active | tinyint(3) UNSIGNED | NO | 0 | Status aktif (0: tidak, 1: aktif) |
| is_verified | tinyint(3) UNSIGNED | NO | 0 | Status verifikasi (0: tidak, 1: terverifikasi) |
| is_verified_email | tinyint(3) UNSIGNED | NO | 0 | Verifikasi email (0: tidak, 1: terverifikasi) |
| is_verified_phone | tinyint(3) UNSIGNED | NO | 0 | Verifikasi telepon (0: tidak, 1: terverifikasi) |
| kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan |
| kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| kelurahan_code | varchar(20) | YES | NULL | Kode kelurahan/desa |
| kelurahan_name | varchar(100) | YES | NULL | Nama kelurahan/desa |
| dukuh | varchar(80) | YES | NULL | Nama dukuh |
| rt | varchar(10) | YES | NULL | Nomor RT |
| rw | varchar(10) | YES | NULL | Nomor RW |
| extra | longtext | YES | NULL | Data tambahan (JSON) |
| fcm | varchar(255) | YES | NULL | Firebase Cloud Messaging token |
| create_datetime | datetime | YES | NULL | Waktu pembuatan |
| update_datetime | datetime | YES | NULL | Waktu pembaruan terakhir |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 2. admin_auth
**Deskripsi**: Tabel menyimpan token autentikasi admin

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| auth_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| auth_admin_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id |
| auth_token | varchar(255) | YES | NULL | Token autentikasi |
| auth_create_datetime | datetime | YES | NULL | Waktu pembuatan token |
| auth_expire_datetime | datetime | YES | NULL | Waktu kadaluarsa token |
| auth_extra | longtext | YES | NULL | Data tambahan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 3. admin_role
**Deskripsi**: Tabel role dan permission untuk admin

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| admin_role_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| admin_role_name | varchar(50) | YES | NULL | Nama role |
| admin_role_access | longtext | YES | NULL | List akses/permission (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 4. ajuan
**Deskripsi**: Tabel master data ajuan/permohonan layanan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajuan_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| ajuan_layanan_kode | varchar(3) | YES | NULL | Kode layanan |
| ajuan_jenis_ajuan_id | tinyint(3) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajuan_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id (pelapor) |
| ajuan_pelapor_nik | varchar(16) | YES | NULL | NIK pelapor |
| ajuan_pelapor_kk | varchar(16) | YES | NULL | KK pelapor |
| ajuan_pelapor_role_id | int(10) UNSIGNED | NO | 0 | Role ID pelapor |
| ajuan_pelapor_role_name | varchar(50) | YES | NULL | Role name pelapor |
| ajuan_is_online | tinyint(3) UNSIGNED | NO | 1 | Online/offline (0: offline, 1: online) |
| ajuan_is_mandiri | tinyint(3) UNSIGNED | NO | 1 | Mandiri (0: multi ajuan, 1: sendiri) |
| ajuan_status | varchar(30) | YES | NULL | Status ajuan |
| ajuan_kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan |
| ajuan_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| ajuan_kelurahan_code | varchar(20) | YES | NULL | Kode kelurahan |
| ajuan_kelurahan_name | varchar(100) | YES | NULL | Nama kelurahan |
| ajuan_keterangan | longtext | YES | NULL | Keterangan tambahan |
| ajuan_extra | longtext | YES | NULL | Data tambahan (JSON) |
| ajuan_data_ajuan | longtext | YES | NULL | Data ajuan dalam JSON |
| ajuan_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| ajuan_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 5. ajuan_akta_kelahiran
**Deskripsi**: Detail ajuan untuk akta kelahiran

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajakel_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajakel_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajakel_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajakel_nik | varchar(16) | YES | NULL | NIK bayi |
| ajakel_nama_bayi | varchar(100) | YES | NULL | Nama bayi |
| ajakel_jenis_kelamin | varchar(9) | YES | NULL | Jenis kelamin (LAKI-LAKI/PEREMPUAN) |
| ajakel_tempat_lahir | varchar(100) | YES | NULL | Tempat lahir |
| ajakel_tgl_lahir | date | YES | NULL | Tanggal lahir |
| ajakel_tgl_kawin | date | YES | NULL | Tanggal kawin orang tua |
| ajakel_anak_ke | tinyint(3) UNSIGNED | NO | 0 | Anak ke berapa |
| ajakel_nama_ibu | varchar(100) | YES | NULL | Nama ibu |
| ajakel_nama_ayah | varchar(100) | YES | NULL | Nama ayah |
| ajakel_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 6. ajuan_akta_kematian
**Deskripsi**: Detail ajuan untuk akta kematian

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajakem_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajakem_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajakem_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajakem_nik | varchar(16) | YES | NULL | NIK jenazah |
| ajakem_nama_jenazah | varchar(100) | YES | NULL | Nama jenazah |
| ajakem_tgl_kematian | datetime | YES | NULL | Tanggal kematian |
| ajakem_tempat_kematian | varchar(100) | YES | NULL | Tempat kematian |
| ajakem_anak_ke | tinyint(3) UNSIGNED | NO | 0 | Anak ke berapa |
| ajakem_nama_ibu | varchar(100) | YES | NULL | Nama ibu |
| ajakem_nama_ayah | varchar(100) | YES | NULL | Nama ayah |
| ajakem_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 7. ajuan_datang
**Deskripsi**: Detail ajuan untuk surat datang/pindah masuk

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajd_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajd_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajd_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajd_nik | varchar(16) | YES | NULL | NIK |
| ajd_no_pindah | varchar(50) | YES | NULL | Nomor surat pindah |
| ajd_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajd_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 8. ajuan_kia
**Deskripsi**: Detail ajuan untuk KIA (Kartu Identitas Anak)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajkia_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajkia_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajkia_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajkia_nik | varchar(16) | YES | NULL | NIK anak |
| ajkia_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajkia_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 9. ajuan_kk
**Deskripsi**: Detail ajuan untuk Kartu Keluarga (KK)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajkk_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajkk_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajkk_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajkk_kk | varchar(16) | YES | NULL | Nomor KK |
| ajkk_nama_kepala_keluarga | varchar(100) | YES | NULL | Nama kepala keluarga |
| ajkk_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 10. ajuan_ktpel
**Deskripsi**: Detail ajuan untuk KTP Elektronik

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajktpel_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajktpel_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajktpel_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajktpel_nik | varchar(16) | YES | NULL | NIK |
| ajktpel_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajktpel_gol_darah | varchar(2) | YES | NULL | Golongan darah |
| ajktpel_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 11. ajuan_pindah
**Deskripsi**: Detail ajuan untuk surat pindah

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajp_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajp_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajp_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajp_nik | varchar(16) | YES | NULL | NIK |
| ajp_kk | varchar(16) | YES | NULL | Nomor KK |
| ajp_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajp_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 12. ajuan_rekam_jemput
**Deskripsi**: Detail ajuan untuk rekam jemput/pendataan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajrj_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajrj_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajrj_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajrj_nik | varchar(16) | YES | NULL | NIK |
| ajrj_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajrj_alasan | longtext | YES | NULL | Alasan rekam jemput |
| ajrj_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 13. ajuan_review
**Deskripsi**: Review dan rating untuk ajuan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| review_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| review_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| review_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| review_rating | tinyint(3) UNSIGNED | NO | 0 | Rating (1-5) |
| review_content | longtext | YES | NULL | Konten review |
| review_create_datetime | datetime | YES | NULL | Waktu pembuatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 14. ajuan_update_data
**Deskripsi**: Detail ajuan untuk perubahan/update data

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ajud_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ajud_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| ajud_jenis_id | bigint(20) UNSIGNED | NO | 0 | FK ke jenis_ajuan |
| ajud_nik | varchar(16) | YES | NULL | NIK |
| ajud_nama_lengkap | varchar(100) | YES | NULL | Nama lengkap |
| ajud_dokumen | longtext | YES | NULL | File dokumen (JSON) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 15. announcement
**Deskripsi**: Tabel pengumuman untuk user dan admin

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| announcement_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| announcement_title | varchar(200) | YES | NULL | Judul pengumuman |
| announcement_author_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id atau user.id |
| announcement_author_fullname | varchar(100) | YES | NULL | Nama pembuat |
| announcement_type | varchar(20) | YES | NULL | Tipe (user/admin) |
| announcement_content | longtext | YES | NULL | Isi pengumuman |
| announcement_status | varchar(20) | YES | NULL | Status (publish/draft/trash) |
| announcement_extra | longtext | YES | NULL | Data tambahan (JSON) |
| announcement_create_datetime | datetime | YES | NULL | Waktu pembuatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 16. bpp
**Deskripsi**: Berita Pindah Pasir (Surat ijin pemakaman)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| bpp_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| bpp_no_reg | varchar(20) | YES | NULL | Nomor registrasi |
| bpp_nik | varchar(16) | YES | NULL | NIK jenazah |
| bpp_nama | varchar(100) | YES | NULL | Nama jenazah |
| bpp_tempat_lahir | varchar(255) | YES | NULL | Tempat lahir |
| bpp_tanggal_lahir | date | YES | NULL | Tanggal lahir |
| bpp_tempat_meninggal | varchar(255) | YES | NULL | Tempat meninggal |
| bpp_tanggal_meninggal | date | YES | NULL | Tanggal meninggal |
| bpp_alamat | text | YES | NULL | Alamat |
| bpp_rt | int(10) UNSIGNED | NO | 0 | Nomor RT |
| bpp_rw | int(10) UNSIGNED | NO | 0 | Nomor RW |
| bpp_kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan |
| bpp_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| bpp_desa_code | varchar(20) | YES | NULL | Kode desa |
| bpp_desa_name | varchar(100) | YES | NULL | Nama desa |
| bpp_tanggal_pemakaman | date | YES | NULL | Tanggal pemakaman |
| bpp_makam_kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan makam |
| bpp_makam_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan makam |
| bpp_makam_desa_code | varchar(20) | YES | NULL | Kode desa makam |
| bpp_makam_desa_name | varchar(100) | YES | NULL | Nama desa makam |
| bpp_makam_nama | varchar(100) | YES | NULL | Nama tempat pemakaman |
| bpp_makam_kode | varchar(20) | YES | NULL | Kode tempat pemakaman |
| bpp_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| bpp_pelapor_nik | varchar(16) | YES | NULL | NIK pelapor |
| bpp_pelapor_nama | varchar(100) | YES | NULL | Nama pelapor |
| bpp_keluarga_telp_nama | varchar(100) | YES | NULL | Nama kontak keluarga |
| bpp_keluarga_telp_no | varchar(13) | YES | NULL | Telepon keluarga |
| bpp_note | longtext | YES | NULL | Catatan tambahan |
| bpp_status | varchar(30) | YES | NULL | Status permohonan |
| bpp_extra | longtext | YES | NULL | Data tambahan (JSON) |
| bpp_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| bpp_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 17. bpp_tempat_pemakaman
**Deskripsi**: Master data lokasi tempat pemakaman

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| bpptp_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| bpptp_jenis | varchar(60) | YES | NULL | Jenis tempat pemakaman |
| bpptp_nama | varchar(200) | YES | NULL | Nama tempat pemakaman |
| bpptp_alamat | text | YES | NULL | Alamat |
| bpptp_kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan |
| bpptp_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| bpptp_desa_code | varchar(20) | YES | NULL | Kode desa |
| bpptp_desa_name | varchar(100) | YES | NULL | Nama desa |
| bpptp_petugas_nama | varchar(100) | YES | NULL | Nama petugas |
| bpptp_petugas_desa_nama | varchar(100) | YES | NULL | Nama petugas desa |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 18. bpp_tempat_pemakaman_jenis
**Deskripsi**: Master jenis tempat pemakaman

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| bppj_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| bppj_title | varchar(60) | YES | NULL | Jenis pemakaman |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 19. category
**Deskripsi**: Kategori untuk post/blog dan report

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| cat_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| cat_pos | int(10) UNSIGNED | NO | 0 | Urutan tampil |
| cat_title | varchar(200) | YES | NULL | Judul kategori |
| cat_slug | varchar(200) | YES | NULL | Slug URL |
| cat_content | longtext | YES | NULL | Deskripsi kategori |
| cat_type | varchar(20) | NO | 'blog' | Tipe kategori (report/blog) |
| cat_image | longtext | YES | NULL | Gambar kategori |
| cat_extra | longtext | YES | NULL | Data tambahan (JSON) |
| cat_is_active | tinyint(3) UNSIGNED | NO | 1 | Status aktif (0: tidak, 1: aktif) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 20. config
**Deskripsi**: Konfigurasi sistem

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| config_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| config_name | varchar(100) | YES | NULL | Nama konfigurasi |
| config_value | longtext | YES | NULL | Nilai konfigurasi |
| config_autoload | tinyint(3) UNSIGNED | NO | 0 | Autoload saat startup (0: tidak, 1: ya) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 21. delivery
**Deskripsi**: Data pengiriman produk hasil ajuan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| delivery_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| delivery_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| delivery_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| delivery_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| delivery_kode | varchar(20) | YES | NULL | Kode pengiriman |
| delivery_resi | varchar(30) | YES | NULL | Nomor resi |
| delivery_proses_kode | varchar(20) | YES | NULL | Kode status proses |
| delivery_sender | text | YES | NULL | Data pengirim (JSON) |
| delivery_receiver | text | YES | NULL | Data penerima (JSON) |
| delivery_receiver_name | varchar(255) | YES | NULL | Nama penerima |
| delivery_receiver_phone | varchar(20) | YES | NULL | Telepon penerima |
| delivery_service | text | YES | NULL | Data layanan kurir (JSON) |
| delivery_status | varchar(30) | YES | NULL | Status (REQUEST/DIKOREKSI/DIPROSES/DISORTIR/SELESAI/DITOLAK) |
| delivery_log | longtext | YES | NULL | Log pengiriman (JSON) |
| delivery_proses_datetime | datetime | YES | NULL | Waktu proses |
| delivery_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| delivery_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 22. delivery_item
**Deskripsi**: Item detail dalam pengiriman

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| delivery_item_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| delivery_item_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| delivery_item_delivery_id | bigint(20) UNSIGNED | NO | 0 | FK ke delivery.id |
| delivery_item_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| delivery_item_prod_id | bigint(20) UNSIGNED | NO | 0 | FK ke produk.id |
| delivery_item_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| delivery_item_layanan_kode | varchar(20) | YES | NULL | Kode layanan |
| delivery_item_prod_nomor | varchar(100) | YES | NULL | Nomor produk |
| delivery_item_prod_nama | varchar(50) | YES | NULL | Nama produk |
| delivery_item_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| delivery_item_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 23. delivery_proses
**Deskripsi**: Status proses pengiriman

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| delivery_proses_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| delivery_proses_kode | varchar(20) | YES | NULL | Kode status proses |
| delivery_proses_create_datetime | datetime | YES | NULL | Waktu pembuatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 24. ilokasi_desa
**Deskripsi**: Master data lokasi desa

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| desa_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| desa_kecamatan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ilokasi_kecamatan.id |
| desa_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| desa_kecamatan_code | varchar(50) | YES | NULL | Kode kecamatan |
| desa_name | varchar(100) | YES | NULL | Nama desa |
| desa_code | varchar(50) | YES | NULL | Kode desa |
| desa_code_bps | varchar(50) | YES | NULL | Kode BPS desa |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 25. ilokasi_kabupaten
**Deskripsi**: Master data lokasi kabupaten

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| kabupaten_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| kabupaten_provinsi_id | bigint(20) UNSIGNED | NO | 0 | FK ke ilokasi_provinsi.id |
| kabupaten_provinsi_name | varchar(100) | YES | NULL | Nama provinsi |
| kabupaten_provinsi_code | varchar(50) | YES | NULL | Kode provinsi |
| kabupaten_name | varchar(100) | YES | NULL | Nama kabupaten |
| kabupaten_code | varchar(50) | YES | NULL | Kode kabupaten |
| kabupaten_code_bps | varchar(50) | YES | NULL | Kode BPS kabupaten |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 26. ilokasi_kecamatan
**Deskripsi**: Master data lokasi kecamatan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| kecamatan_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| kecamatan_kabupaten_id | bigint(20) UNSIGNED | NO | 0 | FK ke ilokasi_kabupaten.id |
| kecamatan_kabupaten_name | varchar(100) | YES | NULL | Nama kabupaten |
| kecamatan_kabupaten_code | varchar(50) | YES | NULL | Kode kabupaten |
| kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| kecamatan_code | varchar(50) | YES | NULL | Kode kecamatan |
| kecamatan_code_bps | varchar(50) | YES | NULL | Kode BPS kecamatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 27. ilokasi_provinsi
**Deskripsi**: Master data lokasi provinsi

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| provinsi_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| provinsi_name | varchar(100) | YES | NULL | Nama provinsi |
| provinsi_code | varchar(50) | YES | NULL | Kode provinsi |
| provinsi_code_bps | varchar(50) | YES | NULL | Kode BPS provinsi |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 28. jenis_ajuan
**Deskripsi**: Master jenis-jenis ajuan/permohonan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| ja_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| ja_judul | varchar(255) | YES | NULL | Judul jenis ajuan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 29. layanan
**Deskripsi**: Master data layanan/service yang disediakan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| layanan_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| layanan_pos | int(10) UNSIGNED | NO | 0 | Urutan tampil |
| layanan_is_layanan | int(10) UNSIGNED | NO | 0 | Flag layanan |
| layanan_is_produk | int(10) UNSIGNED | NO | 0 | Flag produk |
| layanan_nama | varchar(150) | YES | NULL | Nama layanan |
| layanan_desc | longtext | YES | NULL | Deskripsi layanan |
| layanan_kode | varchar(3) | YES | NULL | Kode sistem |
| layanan_image | varchar(255) | YES | NULL | Gambar layanan |
| layanan_extra | longtext | YES | NULL | Data tambahan (JSON) |
| layanan_is_active | tinyint(3) UNSIGNED | NO | 0 | Status aktif (0: tidak, 1: aktif) |
| layanan_jenis_ajuan_id_list | varchar(50) | YES | NULL | List ID jenis ajuan |
| layanan_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| layanan_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 30. layanan_content
**Deskripsi**: Konten detail untuk setiap layanan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| lc_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| lc_author_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id atau user.id |
| lc_author_fullname | varchar(200) | YES | NULL | Nama pembuat konten |
| lc_title | varchar(255) | YES | NULL | Judul konten |
| lc_slug | varchar(255) | YES | NULL | Slug URL |
| lc_type | varchar(20) | NO | 'layanan' | Tipe konten |
| lc_layanan_kode | varchar(3) | NO | '' | Kode layanan |
| lc_status | varchar(20) | NO | 'publish' | Status (publish/draft/trash) |
| lc_content | longtext | YES | NULL | Isi konten |
| lc_image | longtext | YES | NULL | Gambar konten |
| lc_extra | longtext | YES | NULL | Data tambahan (JSON) |
| lc_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| lc_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 31. lembar_kerja
**Deskripsi**: Lembar kerja/worksheet untuk tracking ajuan dan produk

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| lk_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| lk_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| lk_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| lk_jenis_ajuan_id | int(10) UNSIGNED | NO | 0 | FK ke jenis_ajuan.id |
| lk_from_layanan_kode | varchar(3) | YES | NULL | Kode layanan asal |
| lk_layanan_kode | varchar(3) | YES | NULL | Kode layanan tujuan |
| lk_is_produk | tinyint(3) UNSIGNED | NO | 0 | Flag produk (0: tidak, 1: ya) |
| lk_ajuan_is_online | tinyint(3) UNSIGNED | NO | 1 | Online/offline (0: offline, 1: online) |
| lk_ajuan_is_mandiri | tinyint(3) UNSIGNED | NO | 1 | Mandiri (0: multi ajuan, 1: sendiri) |
| lk_produk_id | bigint(20) UNSIGNED | NO | 0 | FK ke produk.id |
| lk_pelapor_role_id | int(10) UNSIGNED | NO | 0 | Role ID pelapor |
| lk_pelapor_role_name | varchar(50) | YES | NULL | Role name pelapor |
| lk_status | varchar(30) | YES | NULL | Status proses |
| lk_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| lk_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 32. log_ajuan_status
**Deskripsi**: Log perubahan status ajuan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| log_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| log_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| log_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| log_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| log_status | varchar(30) | YES | NULL | Status baru |
| log_layanan_kode | varchar(3) | YES | NULL | Kode layanan |
| log_note | longtext | YES | NULL | Catatan perubahan |
| log_extra | longtext | YES | NULL | Data tambahan (JSON) |
| log_admin_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id |
| log_create_datetime | datetime | YES | NULL | Waktu perubahan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 33. log_produk_status
**Deskripsi**: Log perubahan status produk

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| log_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| log_produk_id | bigint(20) UNSIGNED | NO | 0 | FK ke produk.id |
| log_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| log_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| log_status | varchar(30) | YES | NULL | Status baru |
| log_layanan_kode | varchar(3) | YES | NULL | Kode layanan |
| log_admin_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id |
| log_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| log_note | longtext | YES | NULL | Catatan perubahan |
| log_extra | longtext | YES | NULL | Data tambahan (JSON) |
| log_create_datetime | datetime | YES | NULL | Waktu perubahan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 34. master_data_dukung
**Deskripsi**: Master data dokumen dukung untuk setiap layanan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| mdadu_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| mdadu_layanan_kode | varchar(3) | YES | NULL | Kode layanan |
| mdadu_judul | varchar(255) | YES | NULL | Judul dokumen |
| mdadu_desc | varchar(255) | YES | NULL | Deskripsi dokumen |
| mdadu_image | longtext | YES | NULL | Gambar/preview dokumen |
| mdadu_is_required | tinyint(3) UNSIGNED | NO | 0 | Wajib (0: tidak, 1: wajib) |
| mdadu_extra | longtext | YES | NULL | Data tambahan (JSON) |
| mdadu_create_datetime | datetime | YES | NULL | Waktu pembuatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 35. migrations
**Deskripsi**: Laravel migration history tracking

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| id | int(10) UNSIGNED | NO | - | Primary Key |
| migration | varchar(255) | NO | - | Migration file name |
| batch | int(11) | NO | - | Batch number |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 36. notification
**Deskripsi**: Notifikasi untuk user/citizen

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| notification_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| notification_user_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| notification_title | varchar(255) | YES | NULL | Judul notifikasi |
| notification_type | varchar(20) | YES | NULL | Tipe notifikasi (ajuan) |
| notification_is_read | tinyint(3) UNSIGNED | NO | 0 | Status baca (0: unread, 1: read) |
| notification_extra | longtext | YES | NULL | Data tambahan (JSON) |
| notification_create_datetime | datetime | YES | NULL | Waktu pembuatan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 37. post
**Deskripsi**: Konten post/artikel blog

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| post_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| post_author_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin.id atau user.id |
| post_author_fullname | varchar(200) | YES | NULL | Nama pembuat |
| post_cat_id | bigint(20) UNSIGNED | NO | 0 | FK ke category.id |
| post_cat_title | varchar(200) | YES | NULL | Judul kategori |
| post_title | varchar(255) | YES | NULL | Judul post |
| post_slug | varchar(255) | YES | NULL | Slug URL |
| post_type | varchar(20) | NO | 'page' | Tipe (page/blog) |
| post_status | varchar(20) | NO | 'publish' | Status (publish/draft/trash) |
| post_content | longtext | YES | NULL | Isi konten |
| post_image | longtext | YES | NULL | Gambar post |
| post_extra | longtext | YES | NULL | Data tambahan (JSON) |
| post_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| post_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 38. produk
**Deskripsi**: Data produk hasil dari ajuan

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| prod_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| prod_ajuan_id | bigint(20) UNSIGNED | NO | 0 | FK ke ajuan.id |
| prod_pelapor_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| prod_ajuan_no_reg | varchar(20) | YES | NULL | Nomor registrasi ajuan |
| prod_nama | varchar(100) | YES | NULL | Nama produk |
| prod_nomor | varchar(50) | YES | NULL | Nomor/kode produk |
| prod_from_layanan_kode | varchar(3) | YES | NULL | Kode layanan asal |
| prod_layanan_kode | varchar(3) | YES | NULL | Kode layanan tujuan |
| prod_status | varchar(30) | YES | NULL | Status produk |
| prod_url | varchar(255) | YES | NULL | URL akses produk (jika digital) |
| prod_extra | longtext | YES | NULL | Data tambahan (JSON) |
| prod_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| prod_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 39. site
**Deskripsi**: Halaman statis untuk website

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| id | bigint(20) UNSIGNED | NO | - | Primary Key |
| pos | int(10) UNSIGNED | NO | 0 | Urutan tampil |
| title | varchar(200) | YES | NULL | Judul halaman |
| slug | varchar(200) | YES | NULL | Slug URL |
| content | longtext | YES | NULL | Isi konten |
| type | varchar(20) | NO | 'site' | Tipe halaman |
| image | longtext | YES | NULL | Gambar halaman |
| extra | longtext | YES | NULL | Data tambahan (JSON) |
| status | varchar(20) | NO | 'publish' | Status (publish/draft/trash) |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 40. user
**Deskripsi**: Data pengguna regular (citizen/masyarakat umum)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| id | bigint(20) UNSIGNED | NO | - | Primary Key |
| username | varchar(100) | YES | NULL | Username login |
| fullname | varchar(100) | YES | NULL | Nama lengkap |
| nik | varchar(16) | YES | NULL | Nomor Identitas |
| kk | varchar(16) | YES | NULL | Nomor Kartu Keluarga |
| email | varchar(200) | YES | NULL | Email address |
| phone | varchar(13) | YES | NULL | Nomor telepon |
| password | varchar(255) | YES | NULL | Password hash |
| image | longtext | YES | NULL | Foto profil |
| swafoto | longtext | YES | NULL | Foto selfie verifikasi |
| level | varchar(30) | NO | 'user' | Level (user/perantara) |
| role_id | bigint(20) UNSIGNED | NO | 0 | FK ke admin_role atau role custom |
| is_active | tinyint(3) UNSIGNED | NO | 0 | Status aktif (0: tidak, 1: aktif) |
| is_verified | tinyint(3) UNSIGNED | NO | 0 | Status verifikasi (0: tidak, 1: terverifikasi) |
| is_verified_email | tinyint(3) UNSIGNED | NO | 0 | Verifikasi email (0: tidak, 1: terverifikasi) |
| is_verified_phone | tinyint(3) UNSIGNED | NO | 0 | Verifikasi telepon (0: tidak, 1: terverifikasi) |
| is_request_update | tinyint(3) UNSIGNED | NO | 0 | Request perubahan data (0: tidak, 1: ya) |
| kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan |
| kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan |
| kelurahan_code | varchar(20) | YES | NULL | Kode kelurahan/desa |
| kelurahan_name | varchar(100) | YES | NULL | Nama kelurahan/desa |
| dukuh | varchar(80) | YES | NULL | Nama dukuh |
| rt | varchar(10) | YES | NULL | Nomor RT |
| rw | varchar(10) | YES | NULL | Nomor RW |
| extra | longtext | YES | NULL | Data tambahan (JSON) |
| quota | longtext | YES | NULL | Kuota/limit penggunaan |
| fcm | varchar(255) | YES | NULL | Firebase Cloud Messaging token |
| role_kabupaten_name | varchar(100) | YES | NULL | Nama kabupaten role |
| role_kabupaten_code | varchar(20) | YES | NULL | Kode kabupaten role |
| role_kecamatan_name | varchar(100) | YES | NULL | Nama kecamatan role |
| role_kecamatan_code | varchar(20) | YES | NULL | Kode kecamatan role |
| role_kelurahan_name | varchar(100) | YES | NULL | Nama kelurahan role |
| role_kelurahan_code | varchar(20) | YES | NULL | Kode kelurahan role |
| create_datetime | datetime | YES | NULL | Waktu pembuatan |
| update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 41. user_auth
**Deskripsi**: Authentication token untuk user

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| auth_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| auth_user_id | bigint(20) UNSIGNED | NO | 0 | FK ke user.id |
| auth_token | varchar(255) | YES | NULL | Token autentikasi |
| auth_create_datetime | datetime | YES | NULL | Waktu pembuatan token |
| auth_expire_datetime | datetime | YES | NULL | Waktu kadaluarsa token |
| auth_extra | longtext | YES | NULL | Data tambahan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

### 42. user_register_verify_data
**Deskripsi**: Data verifikasi saat registrasi pengguna baru

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| rvd_id | bigint(20) UNSIGNED | NO | - | Primary Key |
| rvd_status | varchar(30) | NO | 'PENGAJUAN' | Status (PENGAJUAN/BELUM DIVERIFIKASI/DISETUJUI/DITOLAK) |
| rvd_nik | varchar(16) | YES | NULL | NIK |
| rvd_fullname | varchar(100) | YES | NULL | Nama lengkap |
| rvd_kk | varchar(16) | YES | NULL | Nomor KK |
| rvd_email | varchar(200) | YES | NULL | Email |
| rvd_phone | varchar(16) | YES | NULL | Nomor telepon |
| rvd_userdata | longtext | YES | NULL | Data user (JSON) |
| rvd_token | varchar(255) | YES | NULL | Token verifikasi |
| rvd_note | longtext | YES | NULL | Catatan verifikasi |
| rvd_create_datetime | datetime | YES | NULL | Waktu pembuatan |
| rvd_update_datetime | datetime | YES | NULL | Waktu pembaruan |

**Engine**: InnoDB | **Charset**: utf8mb4

---

## Relasi Antar Tabel (Hardcoded Relations)

### User & Authentication
```
user (1) ──┬── (n) user_auth
           ├── (n) ajuan (sebagai ajuan_pelapor_id)
           ├── (n) produk (sebagai prod_pelapor_id)
           ├── (n) delivery (sebagai delivery_pelapor_id)
           ├── (n) bpp (sebagai bpp_pelapor_id)
           ├── (n) ajuan_review (sebagai review_pelapor_id)
           ├── (n) notification
           └── (1) user_register_verify_data (dari rvd_userdata)
```

### Admin & Authentication
```
admin (1) ──┬── (n) admin_auth
            ├── (n) log_ajuan_status (sebagai log_admin_id)
            ├── (n) log_produk_status (sebagai log_admin_id)
            ├── (1) admin_role (FK ke admin_role_id)
            ├── (n) announcement (sebagai author)
            └── (n) layanan_content (sebagai author)
```

### Ajuan & Detail
```
ajuan (1) ──┬── (n) ajuan_akta_kelahiran
            ├── (n) ajuan_akta_kematian
            ├── (n) ajuan_datang
            ├── (n) ajuan_kia
            ├── (n) ajuan_kk
            ├── (n) ajuan_ktpel
            ├── (n) ajuan_pindah
            ├── (n) ajuan_rekam_jemput
            ├── (n) ajuan_update_data
            ├── (n) ajuan_review
            ├── (n) produk (1 ajuan bisa menghasilkan banyak produk)
            ├── (n) lembar_kerja
            ├── (n) log_ajuan_status
            ├── (n) delivery
            └── (n) delivery_item
```

### Layanan & Content
```
layanan (1) ──┬── (n) layanan_content
              ├── (n) master_data_dukung
              └── (n) ajuan (indirect via layanan_kode)
```

### Lokasi Hierarchy
```
ilokasi_provinsi (1) ── (n) ilokasi_kabupaten
ilokasi_kabupaten (1) ── (n) ilokasi_kecamatan
ilokasi_kecamatan (1) ── (n) ilokasi_desa
```

### Delivery & Items
```
delivery (1) ──┬── (n) delivery_item
               └── (1) delivery_proses (via delivery_proses_kode)

delivery_item (n) ── (1) produk
```

### Produk & Logs
```
produk (1) ──┬── (n) log_produk_status
             └── (n) delivery_item
```

### BPP (Burial Permit)
```
bpp (1) ── (1) bpp_tempat_pemakaman (via bpp_makam_kode)
bpp_tempat_pemakaman (1) ── (n) bpp (banyak pemakaman di lokasi yang sama)
bpp_tempat_pemakaman_jenis (1) ── (n) bpp_tempat_pemakaman
```

### Post & Content
```
category (1) ── (n) post
post (n) ── (1) category (via post_cat_id)
```

---

## Catatan Penting untuk Pembuatan Model Laravel

### 1. **Field JSON yang Perlu Special Handling**
Kolom-kolom dengan tipe `longtext` yang menyimpan JSON perlu di-cast ke array/JSON:
- `admin.extra` → cast array
- `admin.fcm` → string
- `ajuan.extra` → array
- `ajuan.ajuan_data_ajuan` → array
- Semua field dengan pattern `*_dokumen` → array (untuk file uploads)
- Field `*_log` → array
- Semua field dengan pattern `*_extra` → array

### 2. **Status/Enum Fields**
Beberapa field perlu enum casting:
- `admin.level` → Values: 'administrator', 'admin', 'operator'
- `admin.is_active` → Boolean (0/1)
- `ajuan.ajuan_is_online` → Boolean (0/1)
- `ajuan.ajuan_is_mandiri` → Boolean (0/1)
- `ajuan.ajuan_status` → String (berbagai status)
- `user.level` → Values: 'user', 'perantara'
- `delivery.delivery_status` → Values: 'REQUEST','DIKOREKSI','DIPROSES','DISORTIR','SELESAI','DITOLAK'
- `category.cat_type` → Values: 'report', 'blog'
- `post.post_type` → Values: 'page', 'blog'
- `post.post_status` → Values: 'publish', 'draft', 'trash'
- `layanan_content.lc_status` → Values: 'publish', 'draft', 'trash'

### 3. **Timestamps & Dates**
- Kolom `*_datetime` → gunakan `datetime` type
- Kolom `*_tgl` atau `*_tanggal` → gunakan `date` type

### 4. **Image/File Fields**
Kolom dengan tipe `longtext` untuk media:
- `image` - Biasanya menyimpan base64 atau JSON array
- `swafoto` - Sama seperti image
- `kategori_image` - Gambar kategori
- `layanan_image` - Gambar layanan
- Semua field dengan pattern `*_dokumen` → JSON array untuk multiple files

### 5. **Relationships Pattern**
Mayoritas relasi adalah one-to-many dengan pattern:
- FK kolom ending dengan `_id` mereferensi ke tabel lain
- Kolom yang menyimpan code (seperti `layanan_kode`) juga membangun relasi

### 6. **Soft Deletes**
Tidak ada kolom `deleted_at`, tapi ada status field untuk soft delete logic:
- `is_active` field bisa digunakan untuk status aktif/non-aktif
- Status `trash` untuk post dan content

### 7. **FCM & Token Management**
- `admin.fcm` dan `user.fcm` → Firebase Cloud Messaging tokens
- `auth_token` di `admin_auth` dan `user_auth` → API tokens dengan expiration

### 8. **Lokasi Hierarchy**
File untuk lokasi bersifat master data dengan structure:
```
Provinsi
  └── Kabupaten
       └── Kecamatan
            └── Desa (Kelurahan)
```

### 9. **Ajuan Polymorphic Detail**
Tabel `ajuan` memiliki relasi polymorphic ke berbagai tabel detail:
- `ajuan_akta_kelahiran`
- `ajuan_akta_kematian`
- `ajuan_datang`
- `ajuan_kia`
- `ajuan_kk`
- `ajuan_ktpel`
- `ajuan_pindah`
- `ajuan_rekam_jemput`
- `ajuan_update_data`

Bisa diimplementasikan dengan polymorphic relationship atau simple belongsTo.

### 10. **Indexing Recommendations**
Untuk performa optimal, pertimbangkan index pada:
- Foreign keys: `*_id` fields
- Status fields: `*_status`
- Code fields: `*_code`, `*_kode`
- Timestamps: `*_datetime`
- User identifiers: `nik`, `kk`, `email`

---

## Tips Menggunakan Dokumentasi Ini dengan ChatGPT/Antigravity

1. **Copy seluruh isi dokumentasi** ke prompt ChatGPT
2. **Tambahkan instruksi spesifik**, contoh:
   ```
   Buatkan model Laravel Eloquent untuk database berikut.
   Setiap model harus:
   - Memiliki fillable properties yang sesuai
   - Memiliki casts untuk JSON fields
   - Memiliki relationships sesuai diagram yang disertakan
   - Memiliki scope untuk query yang sering digunakan
   - Memiliki accessor/mutator untuk field khusus
   ```

3. **Untuk model spesifik**, gunakan tabel detail dalam dokumentasi
4. **Untuk relasi**, refer ke section "Relasi Antar Tabel"
5. **Untuk migration**, gunakan deskripsi kolom sebagai guidance

---

**File ini siap digunakan untuk prompt ChatGPT/Antigravity dalam membuat model Laravel secara otomatis dan detail.**
