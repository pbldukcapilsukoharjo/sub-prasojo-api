# Topologi Infrastruktur — Sub Prasojo API

Dokumen ini mendeskripsikan arsitektur dan topologi infrastruktur untuk proyek **Sub Prasojo API** (Sistem Monitoring Layanan Disdukcapil - Dashboard Eksekutif). Sistem ini menggunakan desain arsitektur modern berbasis Laravel dengan model **Dual-Database Connection** (Read-Write pada database User Dashboard, dan Read-Only pada database operasional lama).

---

## 1. High-Level Architectural Diagram

Diagram berikut menunjukkan bagaimana *request* mengalir dari klien melalui *middleware* keamanan, diproses oleh aplikasi Laravel, memanfaatkan *cache* Redis, dan berinteraksi dengan dua basis data yang berbeda secara bersamaan.

```mermaid
graph TD
    %% Styling
    classDef client fill:#3b82f6,stroke:#1d4ed8,stroke-width:2px,color:#fff;
    classDef ingress fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff;
    classDef app fill:#8b5cf6,stroke:#6d28d9,stroke-width:2px,color:#fff;
    classDef cache fill:#ef4444,stroke:#b91c1c,stroke-width:2px,color:#fff;
    classDef db fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff;
    classDef ext fill:#6b7280,stroke:#374151,stroke-width:2px,color:#fff;

    %% Nodes
    subgraph Client_Layer ["Klien & Konsumen API"]
        A["Dashboard Client (React/Vue/Mobile Web)"]:::client
        B["Sistem Operasional Lama (Masyarakat/Operator)"]:::client
    end

    subgraph Ingress_Layer ["Ingress & Middleware Keamanan"]
        C["Nginx HTTP Server (Port 80/443)"]:::ingress
        D["Route Middlewares <br> (Throttle & URL Signatures)"]:::ingress
        E["PASETO Auth Middleware"]:::ingress
    end

    subgraph App_Layer ["Laravel Core (sub-prasojo-api)"]
        F["Laravel Framework (v10.x/11.x)"]:::app
        G["Controllers Layer (Rest JSON Responses)"]:::app
        H["Service Layer (Business Logic & Aggregation)"]:::app
        I["Filter Layer (Parameter Filtering & Normalization)"]:::app
        J["Eloquent Models Layer (Dual DB Bindings)"]:::app
    end

    subgraph Memory_Layer ["Cache & Queue Tier"]
        K["Redis Server (Port 6379)"]:::cache
    end

    subgraph DB_Layer ["Database Tier (Dual DB)"]
        L[("Database Baru (baru_prasojo) <br> mysql Connection [Read-Write]")]:::db
        M[("Database Lama (sukoharjokab_prasojo) <br> mysql_prasojo Connection [READ-ONLY]")]:::db
    end

    subgraph External_Services ["Services Eksternal"]
        N["Mail Transfer Agent (MTA) / SMTP Server"]:::ext
    end

    %% Connections
    A -->|"HTTPS API Requests"| C
    C --> D
    D --> E
    E --> F
    F --> G
    G --> I
    I --> H
    H --> J
    
    %% Redis connections
    F -->|"Cache KPIs & Dashboard"| K
    F -->|"Dispatch Email Queues"| K
    K -->|"Process Mail Jobs"| N
    N -->|"Verification & Reset Link"| A

    %% Dual DB connections
    J -->|"Read-Write User & Token Session"| L
    J -->|"READ-ONLY Queries (Pengajuan, SLA, Ulasan)"| M

    %% Operators writing to old db (External to this API scope)
    B -.->|"Write Operations (Original System)"| M
```

---

## 2. Diagram Aliran Data Dual-Database (Dual Connection Mapping)

Proyek ini menggunakan dua koneksi basis data yang harus dipisahkan dengan ketat untuk menjaga integritas data operasional:

```mermaid
graph LR
    %% Styling
    classDef main fill:#8b5cf6,stroke:#6d28d9,stroke-width:2px,color:#fff;
    classDef conn fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff;
    classDef write fill:#3b82f6,stroke:#1d4ed8,stroke-width:2px,color:#fff;
    classDef read fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff;

    %% Nodes
    App["Eloquent Connection Manager"]:::main
    
    subgraph Connection_MySQL ["Koneksi: 'mysql' (Default)"]
        direction TB
        C1[("Database: baru_prasojo (MySQL/MariaDB)")]:::conn
        T1["Tabel: sub_users (Dashboard Auth)"]:::write
        T2["Tabel: refresh_tokens (Dashboard Session)"]:::write
        T3["Tabel: password_reset_tokens"]:::write
        C1 --- T1
        C1 --- T2
        C1 --- T3
    end

    subgraph Connection_Prasojo ["Koneksi: 'mysql_prasojo'"]
        direction TB
        C2[("Database: lama_prasojo (MariaDB - READ-ONLY)")]:::conn
        T4["Tabel: ajuan (Indexed Columns)"]:::read
        T5["Tabel: admin (Operator & Staff)"]:::read
        T6["Tabel: ajuan_review (Review/Rating)"]:::read
        T7["Tabel: layanan (Master Layanan)"]:::read
        T8["Tabel: lembar_kerja (Alur Berkas)"]:::read
        T9["Tabel: produk (Output & File URL)"]:::read
        C2 --- T4
        C2 --- T5
        C2 --- T6
        C2 --- T7
        C2 --- T8
        C2 --- T9
    end

    App -->|"Read-Write (User Management)"| Connection_MySQL
    App -->|"READ-ONLY (Dashboard & Monitoring)"| Connection_Prasojo
```

---

## 3. Penjelasan Komponen Infrastruktur

### A. Klien & API Gateway (Ingress)
1. **Client Tier**: Executive Dashboard Frontend yang mengonsumsi endpoint API `/api/v1/*`. Membutuhkan performa tinggi karena memuat grafik trend, volume wilayah, dan peringkat operator secara *real-time*.
2. **Route Middleware (Throttle)**:
   - Membatasi *rate limiting* untuk mencegah brute-force dan eksploitasi API (misalnya `throttle:10,1` pada modul otentikasi login/register, dan `throttle:3,1` pada forgot password).
3. **Signed URLs**:
   - Memverifikasi otentisitas link verifikasi pendaftaran email melalui tanda tangan URL Laravel yang ditandatangani secara kriptografis (`middleware(['signed'])`).
4. **PASETO Authentication**:
   - Menggunakan **PASETO (Platform-Agnostic Security Tokens)** `Local v2` untuk pertukaran token secara *stateless* dan aman, menggantikan JWT konvensional.

### B. Aplikasi Core (Laravel - `sub-prasojo-api`)
1. **Controller Layer**:
   - Bertanggung jawab memvalidasi input *payload* melalui *Form Request* dan mengembalikan respon JSON terstandarisasi (Format Sukses `2xx` / Format Gagal `4xx` & `5xx` dalam Bahasa Indonesia).
2. **Service Layer**:
   - Memisahkan logika bisnis dari Controller (seperti perhitungan SLA, agregasi volume wilayah, perhitungan komparasi, dan *ranking* operator) untuk kepatuhan terhadap *Service Pattern* & *Clean Architecture*.
3. **Filter Layer**:
   - Melakukan standardisasi input penyaringan global seperti format tanggal dari `dd-mm-yyyy` (format input API) menjadi format `datetime` standar database melalui `Carbon::createFromFormat('d-m-Y', $value)`.
4. **Enums Layer**:
   - Mendefinisikan status-status transaksi (seperti status lembar kerja dan status produk) di tingkat kode Laravel guna menghindari adanya *hardcoded string* di tingkat *query*.

### C. Cache & Queue (Redis)
1. **Redis Cache Store**:
   - Digunakan untuk menyimpan hasil kalkulasi agregasi KPI Dashboard yang memakan sumber daya besar. Caching ini diimplementasikan di tingkat Service Layer untuk mengurangi beban pembacaan berulang ke database operasional.
2. **Redis Queue Connection**:
   - Digunakan untuk antrean pemrosesan tugas asinkronus (misalnya pengiriman email verifikasi dan pengubahan kata sandi) agar respon API tetap cepat tanpa terhambat proses I/O SMTP email.

### D. Dual-Database Tier
1. **Database Baru (`baru_prasojo`)**:
   - Koneksi default (`mysql`).
   - Sifat: **Read-Write**.
   - Berisi data manajemen pengguna aplikasi dashboard: `sub_users`, `refresh_tokens`, dan `password_reset_tokens`.
2. **Database Lama (`lama_prasojo` / `sukoharjokab_prasojo`)**:
   - Koneksi khusus (`mysql_prasojo`).
   - Sifat: **READ-ONLY**.
   - Berisi data operasional lama Disdukcapil (ajuan, admin, layanan, lembar_kerja, produk, review, ulasan, wilayah, dsb).
   - Pengoptimalan: Migrasi dilakukan hanya untuk menambahkan indeks (Index) guna menunjang kecepatan query visualisasi data tanpa mengubah struktur tabel operasional asli.

---

## 4. Mekanisme Keamanan & Optimasi Kinerja

Untuk menjamin kestabilan dan performa tinggi pada infrastruktur ini, aturan-aturan berikut wajib diterapkan secara ketat:

### 1. Read-Only Enforcement (Koneksi Prasojo)
Seluruh interaksi data operasional lama (koneksi `mysql_prasojo`) tidak diperkenankan melakukan aksi perubahan data (INSERT, UPDATE, DELETE). Eloquent Model yang terhubung dengan tabel operasional diset dengan `$timestamps = false` dan mematikan fungsi penulisan default Laravel.

### 2. Optimasi Database Indexing
Untuk mempercepat pembacaan data statistik dari tabel operasional `ajuan` yang memiliki jutaan baris data, indeks ditambahkan ke kolom-kolom strategis berikut:
- `ajuan_status` (Untuk memilah berkas selesai, ditolak, dll)
- `ajuan_create_datetime` & `ajuan_update_datetime` (Penyaringan berdasarkan periode dan komparasi SLA)
- `ajuan_no_reg` (Pencarian cepat nomor registrasi)
- `ajuan_kecamatan_code` (Penyaringan wilayah)
- `ajuan_is_online` & `ajuan_pelapor_role_name` (Penyaringan jalur pelaporan)
- `ajuan_pelapor_id` (Relasi monitoring operator)

### 3. Pencegahan N+1 Query (CRITICAL)
N+1 query dilarang keras karena dapat memperlambat pemrosesan dan membebani server database.
- **Eager Loading**: Semua relasi model Eloquent yang akan diproses dalam perulangan (*loop*) atau diserialisasikan ke JSON (seperti relasi `pelapor` dan `layanan` pada `Ajuan`) wajib dimuat menggunakan `with()` atau `loadMissing()`.
- **Custom SQL Aggregation**: Menggunakan `DB::raw()` atau query *join* jika pengambilan data relasional terlalu kompleks agar perhitungan statistik diselesaikan langsung di tingkat mesin database dalam satu kali query tunggal.
