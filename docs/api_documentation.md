# API Documentation
**Proyek:** Sistem Monitoring Layanan Disdukcapil
**Autentikasi:** PASETO (Platform-Agnostic Security Tokens)
**Base URL:** `{{base_url}}/api`

---

## Keterangan Status Endpoint
- 🟢 **[SAMA]**: Endpoint sama dengan desain awal di `api-1.json`
- 🔵 **[UPDATED]**: Endpoint mengalami perubahan path atau struktur dari `api-1.json`
- 🟡 **[BARU]**: Endpoint baru yang tidak ada di `api-1.json`
- ❌ **[TIDAK DIPAKAI]**: Endpoint tidak lagi digunakan (deprecated) dan digantikan oleh endpoint lain (misalnya `/pengajuan`).
- ✅ **[BERFUNGSI]**: Endpoint sudah selesai diimplementasi di sisi *backend* dan berfungsi normal.
- 🚧 **[BELUM BERFUNGSI]**: Endpoint masih dalam tahap pengembangan atau *draft*.

*(Saat ini seluruh endpoint yang aktif (tidak ditandai ❌) telah selesai diimplementasikan di sisi backend)*.

---

## Standard Error Responses
Untuk menghindari repetisi, seluruh API (kecuali Login) akan mengembalikan *error* **401 Unauthorized** jika token tidak dikirim atau tidak valid:
```json
{
  "status": false,
  "code": 401,
  "message": "Akses ditolak. Token PASETO tidak valid atau kedaluwarsa.",
  "data": null
}
```

Jika ada validasi parameter/body yang gagal, sistem akan selalu me-return **400 Bad Request**:
```json
{
  "status": false,
  "code": 400,
  "message": "Validasi gagal. Silakan periksa kembali input Anda.",
  "data": {
    "nama_parameter": ["Pesan error spesifik dari backend (bahasa indonesia)"]
  }
}
```

---

## 1. Modul Auth & Profile

### 1.1 Login 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/login`
**Deskripsi:** Memverifikasi kredensial pengguna dan mengembalikan token akses.

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Ya | Email yang terdaftar |
| `password` | `string` | Ya | Kata sandi |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Login berhasil",
  "data": {
    "token": "v2.local.xxxxx...",
    "expires_in": 3600
  }
}
```

**Response Error (401 Unauthorized - Kredensial Salah):**
```json
{
  "status": false,
  "code": 401,
  "message": "Email atau password yang Anda masukkan salah.",
  "data": null
}
```

### 1.2 Refresh Token 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/refresh`
**Deskripsi:** Memperbarui akses token yang akan kedaluwarsa.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Token berhasil diperbarui",
  "data": {
    "token": "v2.local.yyyyy...",
    "expires_in": 3600
  }
}
```

### 1.3 Get Profile (Me) 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/me`)*
**Endpoint:** `GET /api/v1/auth/me`
**Deskripsi:** Mengambil detail profil user yang sedang login.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data profil",
  "data": {
    "id": 1,
    "email": "operator@disdukcapil.go.id",
    "created_at": "2024-01-01 10:00:00"
  }
}
```

### 1.4 Update Profile 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `PUT /api/v1/auth/profile`
**Deskripsi:** Memperbarui data pengguna.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Tidak | Ubah email |
| `password` | `string` | Tidak | Isi jika ingin ganti password |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Profil berhasil diperbarui",
  "data": null
}
```

### 1.5 Logout 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/logout`
**Deskripsi:** Menghancurkan sesi pengguna (Blacklist Token).
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Logout berhasil",
  "data": null
}
```

### 1.6 Register 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/register`
**Deskripsi:** Mendaftarkan pengguna baru ke sistem.

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `fullname` | `string` | Ya | Nama lengkap |
| `email` | `string` | Ya | Email valid & unik |
| `password` | `string` | Ya | Kata sandi (min. 8 karakter) |
| `password_confirmation` | `string` | Ya | Konfirmasi kata sandi |

**Response Sukses (201 Created):**
```json
{
  "status": true,
  "code": 201,
  "message": "Registrasi berhasil",
  "data": {
    "id": 2,
    "email": "user@example.com",
    "fullname": "John Doe",
    "created_at": "2024-01-01 10:00:00"
  }
}
```

### 1.7 Lupa Password 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/forgot-password`
**Deskripsi:** Mengirimkan email untuk mengubah password.

### 1.8 Reset Password 🟢 [SAMA] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/auth/reset-password`
**Deskripsi:** Mengubah password berdasarkan token yang diterima.

### 1.9 Verifikasi Email (Notice) 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/auth/email/verify`)*
**Endpoint:** `GET /api/v1/email/verify`
**Deskripsi:** Middleware notifikasi jika email belum diverifikasi.

### 1.10 Verifikasi Email (Action) 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/auth/email/verify/{id}/{hash}`)*
**Endpoint:** `GET /api/v1/email/verify/{id}/{hash}`
**Deskripsi:** URL yang di-klik pengguna untuk memverifikasi email.

### 1.11 Resend Verifikasi Email 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `POST /api/v1/auth/email/resend`)*
**Endpoint:** `POST /api/v1/email/resend`
**Deskripsi:** Mengirim ulang email verifikasi.

---

## 2. Modul Pengajuan (Ajuan, Lembar Kerja, Produk)

### 2.1 List Lembar Kerja 🔵 [UPDATED] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/pengajuan/lembar-kerja`
**Deskripsi:** Menampilkan list pengajuan untuk halaman lembar kerja.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data",
  "data": [
    {
      "ajuan_id": 1,
      "ajuan_no_reg": "REG-12345",
      "ajuan_create_datetime": "2024-01-01 10:00:00",
      "ajuan_status": "DIPROSES",
      "ajuan_pelapor_role_name": "Online",
      "ajuan_is_online": 1,
      "kecamatan": {
         "kecamatan_id": 1,
         "kecamatan_name": "Klojen"
      },
      "layanan": {
         "layanan_id": 1,
         "layanan_name": "KTP Elektronik"
      },
      "pelapor": {
         "user_id": 1,
         "user_nik": "3573010000000001",
         "user_nama_lengkap": "Budi Santoso"
      }
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 100,
    "total_page": 10
  },
  "chart_status": [
    {"status": "DIPROSES", "total": 50},
    {"status": "DITERIMA", "total": 50}
  ],
  "chart_layanan": [
    {"layanan": "KTP Elektronik", "total": 100}
  ]
}
```

### 2.2 List Ajuan 🔵 [UPDATED] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/pengajuan/ajuan`
**Deskripsi:** Menampilkan list pengajuan untuk halaman ajuan.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data",
  "data": [
    {
      "ajuan_id": 1,
      "ajuan_no_reg": "REG-12345",
      "ajuan_create_datetime": "2024-01-01 10:00:00",
      "ajuan_status": "MENUNGGU",
      "ajuan_pelapor_role_name": "Online",
      "ajuan_is_online": 1,
      "kecamatan": {
         "kecamatan_id": 1,
         "kecamatan_name": "Klojen"
      },
      "layanan": {
         "layanan_id": 1,
         "layanan_name": "KTP Elektronik"
      },
      "pelapor": {
         "user_id": 1,
         "user_nik": "3573010000000001",
         "user_nama_lengkap": "Budi Santoso"
      }
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 100,
    "total_page": 10
  }
}
```

### 2.3 List Produk 🔵 [UPDATED] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/pengajuan/produk`
**Deskripsi:** Menampilkan list pengajuan untuk halaman produk, dilengkapi `nama_identitas_produk`.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data",
  "data": [
    {
      "ajuan_id": 1,
      "ajuan_no_reg": "REG-12345",
      "ajuan_create_datetime": "2024-01-01 10:00:00",
      "ajuan_status": "SELESAI",
      "ajuan_is_online": 1,
      "nama_identitas_produk": "Kartu Keluarga a.n Budi",
      "kecamatan": {
         "kecamatan_id": 1,
         "kecamatan_name": "Klojen"
      },
      "layanan": {
         "layanan_id": 1,
         "layanan_name": "KTP Elektronik"
      }
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 100,
    "total_page": 10
  }
}
```

### 2.4 Detail Timeline Pengajuan 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/pengajuan/{ajuan_id}/detail`
**Deskripsi:** Menampilkan detail timeline status dari sebuah pengajuan.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": {
    "ajuan_id": 1,
    "no_reg": "REG-12345",
    "status_saat_ini": "DIPROSES",
    "timeline": [
      {
        "status": "MENUNGGU",
        "note": "Pengajuan baru masuk",
        "datetime": "2024-01-01 10:00:00"
      },
      {
        "status": "DIPROSES",
        "note": "Berkas sedang diverifikasi oleh petugas",
        "datetime": "2024-01-02 11:30:00"
      }
    ]
  }
}
```


*Catatan: Semua endpoint list (2.1 - 2.3) mendukung Query Parameters berikut:*
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `search` | `string` | Tidak | Pencarian cepat (no reg / NIK pelapor / layanan / kecamatan) |
| `kecamatan` | `string` | Tidak | Filter kode kecamatan |
| `pelapor` | `string` | Tidak | Filter nama peran pelapor (hanya untuk Ajuan & Lembar Kerja) |
| `start_date` | `string` | Tidak | Tanggal awal (format `dd-mm-yyyy`) (hanya untuk Ajuan & Lembar Kerja) |
| `end_date` | `string` | Tidak | Tanggal akhir (format `dd-mm-yyyy`) (hanya untuk Ajuan & Lembar Kerja) |
| `periode` | `integer` | Tidak | Filter periode berdasarkan bulan (1-12) |
| `layanan` | `string` | Tidak | Filter kode layanan (untuk filter tab bar) |
| `nama_identitas_produk` | `string` | Tidak | Filter berdasarkan nama identitas produk (HANYA untuk Produk) |
| `sort` | `string` | Tidak | Urutan data (`terbaru` atau `terlama`) |
| `per_page` | `integer` | Tidak | Jumlah data per halaman paginasi (default 10) |
| `page` | `integer` | Tidak | Nomor halaman paginasi |

---

## 3. Modul Dashboard

### 3.1 Dashboard KPI 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: digabung dalam `GET /api/v1/dashboard`)*
**Endpoint:** `GET /api/v1/dashboard/kpi`
**Deskripsi:** Mengambil 4 KPI utama. Mendukung rentang global filter.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`
**Query Params:** Mendukung standar rentang waktu, `id_kecamatan`, `id_layanan`.

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil KPI Dashboard",
  "data": {
    "total_pengajuan": 15000,
    "total_pengajuan_trend_persen": 12.5,
    "total_selesai": 12500,
    "total_selesai_trend_persen": 8.0,
    "total_ditolak": 2500,
    "total_ditolak_trend_persen": -5.2,
    "rata_rata_sla_jam": 2.5,
    "rata_rata_sla_trend_persen": 1.5,
    "rata_rata_sla_text": "2 Jam 30 Menit"
  }
}
```

### 3.2 Dashboard Trend Chart 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/dashboard/chart-trend`
**Deskripsi:** Mengambil array pergerakan data untuk *Line Chart*.

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
    { "tanggal": "2024-01-01", "total_ajuan": 120, "selesai": 100 },
    { "tanggal": "2024-01-02", "total_ajuan": 150, "selesai": 130 }
  ]
}
```

### 3.3 Dashboard Top Wilayah 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/dashboard/top-wilayah`
**Deskripsi:** Mengambil top 5 wilayah penyumbang ajuan terbanyak (*Bar chart*).

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
    { "id_kecamatan": 1, "nama_kecamatan": "Klojen", "total": 4500 },
    { "id_kecamatan": 2, "nama_kecamatan": "Lowokwaru", "total": 4000 }
  ]
}
```

---

## 4. Modul Monitoring Operator

### 4.1 Operator KPI Global 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/operator/kpi-global`
**Query Params Khusus:** Filter standar, `id_kecamatan`. (Tidak ada `id_layanan`).

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": {
    "total_aktif": 25,
    "total_berkas_dikerjakan": 10500,
    "rata_rata_kecepatan_text": "30 Menit/Berkas"
  }
}
```

### 4.2 Ranking Operator 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/dashboard/peringkat-operator`)*
**Endpoint:** `GET /api/v1/operator/ranking`
**Deskripsi:** Menampilkan urutan ranking kecepatan. Mendukung `search_nama` dan filter kecamatan.

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
      {
        "peringkat": 1,
        "id_operator": 45,
        "nama": "Budi Santoso",
        "total_berkas": 1200,
        "rata_rata_waktu_menit": 15
      }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 45,
    "total_page": 5
  }
}
```

### 4.3 Detail Operator ❌ [TIDAK DIPAKAI]
**Endpoint:** `GET /api/v1/operator/{id_operator}/detail`
**Deskripsi:** Endpoint ini sudah tidak dipakai dan data/riwayat kerja digantikan melalui pencarian di endpoint master `/api/v1/pengajuan`.

### 4.4 Export Ranking Operator 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/operator/export`
**Deskripsi:** Mengekspor tabel urutan ranking operator dalam format Excel (`.xlsx`). Parameter query (seperti pencarian atau filter wilayah) sama dengan endpoint `GET /api/v1/operator/ranking` namun mengabaikan aturan paginasi.

---

## 5. Modul Monitoring Wilayah

### 5.1 Distribusi Wilayah 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/dashboard/distribusi-wilayah`)*
**Endpoint:** `GET /api/v1/wilayah/distribusi`
**Deskripsi:** List volume per kecamatan. (Filter standar & `id_kecamatan` spesifik, *no search*, *no layanan*).

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
      {
        "id_kecamatan": 1,
        "nama_kecamatan": "Klojen",
        "total_ajuan": 4500,
        "rata_rata_waktu": "2 Jam",
        "rasio_selesai_persen": 95.5
      }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 5,
    "total_page": 1
  }
}
```

### 5.2 Export Distribusi Wilayah 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/wilayah/export`
**Deskripsi:** Mengekspor tabel volume per kecamatan ke dalam file Excel (`.xlsx`). Semua parameter filter yang dikirimkan pada `GET /api/v1/wilayah/distribusi` akan berlaku untuk data yang diekspor.

---

## 6. Modul SLA Monitoring

### 6.1 KPI SLA 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/sla/kpi`
**Deskripsi:** Menampilkan rata-rata proses & rasio pencapaian SLA.

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": {
    "rata_rata_global_text": "3 Jam 15 Menit",
    "capaian_sla_persen": 88.5
  }
}
```

### 6.2 Tabel Komparasi Layanan 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/dashboard/waktu-rata`)*
**Endpoint:** `GET /api/v1/sla/layanan`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
      {
        "layanan_kode": "KTP-EL",
        "nama_layanan": "KTP Elektronik",
        "target_sla": "24 Jam",
        "aktual_rata_rata": "18 Jam",
        "status_sla": "MEMENUHI"
      }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 15,
    "total_page": 2
  }
}
```

### 6.3 Export Tabel Komparasi Layanan 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/sla/export`
**Deskripsi:** Mengekspor data komparasi pemenuhan SLA layanan ke format file Excel (`.xlsx`). Parameter disesuaikan dengan yang aktif pada tabel data SLA.

---

## 7. Modul Monitoring Ulasan

### 7.1 Ulasan KPI 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/ulasan/kpi`
**Deskripsi:** Hero score ulasan dan hitungan per bintang.

**Query Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `start_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal mulai review. |
| `end_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal akhir review. |
| `layanan_kode` | `string` | Tidak | Filter berdasarkan jenis/layanan. |
| `rating` | `integer` | Tidak | Filter bintang rating (1-5). |
| `search` | `string` | Tidak | Pencarian berdasarkan isi komentar atau nomor registrasi. |
| `sort_by` | `string` | Tidak | Field untuk sorting (e.g., `newest`, `oldest`, `rating_asc`, `rating_desc`). |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": {
    "rata_rata_bintang": 4.5,
    "distribusi": {
      "bintang_5": 1200,
      "bintang_4": 400,
      "bintang_3": 50,
      "bintang_2": 10,
      "bintang_1": 5
    }
  }
}
```

### 7.2 List Ulasan 🔵 [UPDATED] ✅ [BERFUNGSI]
*(Sebelumnya di `api-1.json`: `GET /api/v1/dashboard/ulasan`)*
**Endpoint:** `GET /api/v1/ulasan`
**Deskripsi:** List data ulasan yang dikirim oleh masyarakat beserta detail pengajuannya.

**Query Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `start_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal mulai review. |
| `end_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal akhir review. |
| `layanan_kode` | `string` | Tidak | Filter berdasarkan jenis/layanan. |
| `rating` | `integer` | Tidak | Filter bintang rating (1-5). |
| `search` | `string` | Tidak | Pencarian berdasarkan isi komentar atau nomor registrasi. |
| `sort_by` | `string` | Tidak | Field untuk sorting (e.g., `newest`, `oldest`, `rating_asc`, `rating_desc`). |
| `page` | `integer` | Tidak | Halaman paginasi (default: 1). |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": [
      {
        "id_review": 1,
        "tanggal": "2024-01-01",
        "no_reg": "REG-123",
        "layanan": "KTP-el",
        "rating": 5,
        "komentar": "Pelayanan sangat cepat dan memuaskan!"
      }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 1500,
    "total_page": 150
  }
}
```

### 7.3 Export Ulasan 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/ulasan/export`
**Deskripsi:** Mengunduh data ulasan dalam format Excel (.xlsx).

**Query Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `start_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal mulai review. |
| `end_date` | `string` | Tidak | Format `dd-mm-yyyy`. Filter tanggal akhir review. |
| `layanan_kode` | `string` | Tidak | Filter berdasarkan jenis/layanan. |
| `rating` | `integer` | Tidak | Filter bintang rating (1-5). |
| `search` | `string` | Tidak | Pencarian berdasarkan isi komentar atau nomor registrasi. |
| `sort_by` | `string` | Tidak | Field untuk sorting (e.g., `newest`, `oldest`, `rating_asc`, `rating_desc`). |

**Response Sukses (200 OK):**
Header response akan mengatur *Content-Type* dan *Content-Disposition* untuk pengunduhan file.
```http
HTTP/1.1 200 OK
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="export_ulasan_20240101_20240131.xlsx"

<binary_data>

---

## 8. Modul Master Hari Libur

### 8.1 List Hari Libur 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/holidays`
**Deskripsi:** Menampilkan daftar hari libur nasional dengan filter tahun dan pencarian keterangan.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Query Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `tahun` | `integer` | Tidak | Filter berdasarkan tahun (contoh: `2027`) |
| `search` | `string` | Tidak | Pencarian berdasarkan keterangan |
| `page` | `integer` | Tidak | Halaman paginasi (default: 1) |
| `per_page` | `integer` | Tidak | Jumlah item per halaman (default: 15, max: 100) |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data hari libur",
  "data": [
    {
      "id": 1,
      "tanggal": "2027-01-01",
      "keterangan": "Tahun Baru 2027 Masehi",
      "created_at": "2026-08-01T09:00:00.000000Z",
      "updated_at": "2026-08-01T09:00:00.000000Z"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 1,
    "total_page": 1
  }
}
```

### 8.2 Tambah Hari Libur (Single & Bulk) 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/holidays`
**Deskripsi:** Menambahkan satu atau beberapa data hari libur nasional sekaligus (bulk insert).
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `holidays` | `array` | Ya | Array objek hari libur (minimal 1 item) |
| `holidays.*.tanggal` | `string` | Ya | Tanggal libur (format `YYYY-MM-DD`, harus unik & belum ada di DB) |
| `holidays.*.keterangan` | `string` | Ya | Keterangan hari libur (max: 255) |

**Response Sukses (201 Created):**
```json
{
  "status": true,
  "code": 201,
  "message": "Berhasil menambahkan data hari libur",
  "data": [
    {
      "id": 1,
      "tanggal": "2027-01-01",
      "keterangan": "Tahun Baru 2027 Masehi",
      "created_at": "2026-08-01T09:00:00.000000Z",
      "updated_at": "2026-08-01T09:00:00.000000Z"
    }
  ]
}
```

### 8.3 Download Template Excel 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/holidays/template`
**Deskripsi:** Mengunduh template file Excel (.xlsx) untuk panduan pengisian data hari libur.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
Header response:
```http
HTTP/1.1 200 OK
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="template_hari_libur.xlsx"
```

### 8.4 Import Hari Libur dari Excel 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `POST /api/v1/holidays/import`
**Deskripsi:** Mengimpor data hari libur nasional dari file Excel. Menggunakan transaksi **full rollback** apabila terdapat duplikasi tanggal (internal dalam file maupun dengan data di database).
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Body Parameters (multipart/form-data):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `file` | `file` | Ya | File Excel (.xlsx, .xls, max: 2048 KB) |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengimpor 2 data hari libur",
  "data": [
    {
      "id": 1,
      "tanggal": "2027-01-01",
      "keterangan": "Tahun Baru 2027 Masehi"
    }
  ]
}
```

### 8.5 Detail Hari Libur 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `GET /api/v1/holidays/{id}`
**Deskripsi:** Menampilkan detail satu data hari libur berdasarkan ID.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil detail hari libur",
  "data": {
    "id": 1,
    "tanggal": "2027-01-01",
    "keterangan": "Tahun Baru 2027 Masehi",
    "created_at": "2026-08-01T09:00:00.000000Z",
    "updated_at": "2026-08-01T09:00:00.000000Z"
  }
}
```

### 8.6 Update Hari Libur 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `PUT /api/v1/holidays/{id}` / `PATCH /api/v1/holidays/{id}`
**Deskripsi:** Memperbarui tanggal atau keterangan satu data hari libur.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `tanggal` | `string` | Ya | Tanggal libur baru (format `YYYY-MM-DD`, harus unik) |
| `keterangan` | `string` | Ya | Keterangan hari libur (max: 255) |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil memperbarui data hari libur",
  "data": {
    "id": 1,
    "tanggal": "2027-01-02",
    "keterangan": "Cuti Bersama Tahun Baru",
    "created_at": "2026-08-01T09:00:00.000000Z",
    "updated_at": "2026-08-01T09:05:00.000000Z"
  }
}
```

### 8.7 Hapus Hari Libur Single 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `DELETE /api/v1/holidays/{id}`
**Deskripsi:** Menghapus satu data hari libur berdasarkan ID.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil menghapus data hari libur",
  "data": null
}
```

### 8.8 Hapus Hari Libur Bulk 🟡 [BARU] ✅ [BERFUNGSI]
**Endpoint:** `DELETE /api/v1/holidays/bulk`
**Deskripsi:** Menghapus banyak data hari libur sekaligus berdasarkan array ID.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Body Parameters (JSON):**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `ids` | `array` | Ya | Array ID hari libur yang akan dihapus (minimal 1 ID, harus ada di DB) |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil menghapus 3 data hari libur",
  "data": null
}
```
```
