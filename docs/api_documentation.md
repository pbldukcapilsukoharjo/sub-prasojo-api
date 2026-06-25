# API Documentation
**Proyek:** Sistem Monitoring Layanan Disdukcapil
**Autentikasi:** PASETO (Platform-Agnostic Security Tokens)
**Base URL:** `{{base_url}}/api`

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

### 1.1 Login
**Endpoint:** `POST /auth/login`
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

### 1.2 Refresh Token
**Endpoint:** `POST /auth/refresh`
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

### 1.3 Get Profile (Me)
**Endpoint:** `GET /auth/me`
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

### 1.4 Update Profile
**Endpoint:** `PUT /auth/profile`
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

### 1.5 Logout
**Endpoint:** `POST /auth/logout`
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

---

## 2. Modul Pengajuan (Ajuan, Lembar Kerja, Produk)

### 2.1 List Pengajuan
**Endpoint:** `GET /pengajuan`
**Deskripsi:** Endpoint *master* untuk tabel pengajuan (mendukung paginasi). Meng-handle halaman Lembar Kerja, Produk, maupun Semua Ajuan.
**Headers:** `Authorization: Bearer {PASETO_TOKEN}`

**Query Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `status_kategori` | `string` | Ya | Pilihan: `lembar_kerja`, `produk`, `all` |
| `periode_bulan` | `integer` | Tidak | Bulan (1-12). Default: tahun berjalan |
| `start_date` | `string` | Tidak | Rentang khusus (dd-mm-yyyy) |
| `end_date` | `string` | Tidak | Rentang khusus (dd-mm-yyyy) |
| `search_no_reg` | `string` | Tidak | Pencarian berdasarkan No. Registrasi |
| `pelapor` | `string` | Tidak | Jalur pelaporan (e.g., online, mandiri, dll) |
| `id_kecamatan` | `integer` | Tidak | Filter wilayah spesifik |
| `id_layanan` | `integer` | Tidak | Filter layanan spesifik |
| `status` | `string` | Tidak | Memfilter status spesifik `ajuan_status` |
| `sort_by` | `string` | Tidak | Nama kolom pengurutan |
| `sort_dir` | `string` | Tidak | `asc` atau `desc` |
| `page` | `integer` | Tidak | Halaman paginasi |

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data pengajuan",
  "data": [
      {
        "id": 123,
        "no_reg": "REG-20240101-001",
        "layanan": "KTP-el",
        "kecamatan": "Klojen",
        "pelapor": "Pemohon (Online)",
        "status": "MENUNGGU",
        "created_at": "2024-01-01 08:00:00"
      }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 145,
    "total_page": 15
  }
}
```

### 2.2 Export Excel Pengajuan
**Endpoint:** `GET /pengajuan/export`
**Deskripsi:** Mengunduh file `.xlsx`. Parameter sama persis dengan `GET /pengajuan` (tanpa `page`). Mengembalikan tipe file statis langsung (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`).

---

## 3. Modul Dashboard

### 3.1 Dashboard KPI
**Endpoint:** `GET /dashboard/kpi`
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

### 3.2 Dashboard Trend Chart
**Endpoint:** `GET /dashboard/chart-trend`
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

### 3.3 Dashboard Top Wilayah
**Endpoint:** `GET /dashboard/top-wilayah`
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

### 4.1 Operator KPI Global
**Endpoint:** `GET /operator/kpi-global`
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

### 4.2 Ranking Operator
**Endpoint:** `GET /operator/ranking`
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

### 4.3 Detail Operator
**Endpoint:** `GET /operator/{id_operator}/detail`

**Response Sukses (200 OK):**
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil",
  "data": {
    "profil": {
      "nama": "Budi Santoso",
      "total_dikerjakan": 1200,
      "rata_rata_waktu_menit": 15
    },
    "riwayat_kerja": [
      {
        "no_reg": "REG-123",
        "layanan": "KTP",
        "waktu_mulai": "08:00:00",
        "waktu_selesai": "08:10:00",
        "durasi_menit": 10
      }
    ]
  }
}
```

### 4.4 Export Ranking Operator
**Endpoint:** `GET /operator/export`
**Deskripsi:** Mengekspor tabel urutan ranking operator dalam format Excel (`.xlsx`). Parameter query (seperti pencarian atau filter wilayah) sama dengan endpoint `GET /operator/ranking` namun mengabaikan aturan paginasi.

---

## 5. Modul Monitoring Wilayah

### 5.1 Distribusi Wilayah
**Endpoint:** `GET /wilayah/distribusi`
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

### 5.2 Export Distribusi Wilayah
**Endpoint:** `GET /wilayah/export`
**Deskripsi:** Mengekspor tabel volume per kecamatan ke dalam file Excel (`.xlsx`). Semua parameter filter yang dikirimkan pada `GET /wilayah/distribusi` akan berlaku untuk data yang diekspor.

---

## 6. Modul SLA Monitoring

### 6.1 KPI SLA
**Endpoint:** `GET /sla/kpi`
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

### 6.2 Tabel Komparasi Layanan
**Endpoint:** `GET /sla/layanan`

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

### 6.3 Export Tabel Komparasi Layanan
**Endpoint:** `GET /sla/export`
**Deskripsi:** Mengekspor data komparasi pemenuhan SLA layanan ke format file Excel (`.xlsx`). Parameter disesuaikan dengan yang aktif pada tabel data SLA.

---

## 7. Modul Monitoring Ulasan

### 7.1 Ulasan KPI
**Endpoint:** `GET /ulasan/kpi`
**Deskripsi:** Hero score ulasan dan hitungan per bintang. Filter standar + `id_layanan` + `rating`.

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

### 7.2 List Ulasan
**Endpoint:** `GET /ulasan/list`

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

### 7.3 Export Ulasan
**Endpoint:** `GET /ulasan/export`
**Deskripsi:** Mengunduh data ulasan dalam format Excel (.xlsx). Mendukung filter rentang tanggal, layanan, dan rating.

**Response Sukses (200 OK):**
Header response akan mengatur *Content-Type* dan *Content-Disposition* untuk pengunduhan file.
```http
HTTP/1.1 200 OK
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="export_ulasan_20240101_20240131.xlsx"

<binary_data>
```
