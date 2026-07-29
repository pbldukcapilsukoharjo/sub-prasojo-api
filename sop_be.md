# Standar Operasional Prosedur (SOP) - Backend Development
**Project: Monitoring PRASOJO API**

Dokumen ini mendeskripsikan secara spesifik standar, arsitektur, dan pola penulisan kode (*design pattern*) yang wajib ditaati selama proses pengembangan *backend* pada proyek Monitoring PRASOJO API.

---

## 1. Backend Architecture
Proyek ini mengadopsi arsitektur **Service-Oriented** yang dikombinasikan dengan **Dual Database Architecture**. 
*   **API-Only**: Sistem ini murni bertindak sebagai *RESTful API Provider* (tidak memiliki *View/Blade*).
*   **Dual Connection**:
    *   `mysql` (Default): Database yang berisi entitas spesifik milik sistem Monitoring ini (seperti `sub_users`, `ajuan_sla_summaries`). Bersifat **Read & Write**.
    *   `mysql_prasojo`: Database operasional utama yang menyimpan data pelayanan kependudukan. Bersifat **READ-ONLY**.

## 2. Folder Structure
Selain struktur standar Laravel, proyek ini mendefinisikan beberapa direktori khusus di dalam folder `app/`:
*   `app/Http/Controllers/Api/V1/` - Menyimpan seluruh Controller versi 1.
*   `app/Services/` - Menyimpan kelas bisnis logik (*Service Layer*).
*   `app/Filters/` - Menyimpan kelas yang bertanggung jawab men-parsing *Request HTTP* menjadi klausa *Query Eloquent* (misal: `WilayahFilter`).
*   `app/Models/Monitoring/` - Model-model untuk database default (`mysql`).
*   `app/Models/Prasojo/` - Model-model spesifik untuk tabel operasional lama (`mysql_prasojo`).
*   `app/Http/Responses/` - Menyimpan *helper*/konvensi bentuk *Response* JSON.

## 3. Layer Architecture
Aplikasi memisahkan tanggung jawab (Seperation of Concerns) ke dalam 7 layer:

### Route
Berada di `routes/api.php`. Rute dikelompokkan menggunakan `Route::prefix()`, dilindungi oleh autentikasi, dan didefinisikan menggunakan fitur `Route::controller(...)` untuk meminimalisir penulisan rute yang repetitif.

### Middleware
Menggunakan Middleware kustom `PasetoAuth`. Middleware ini **tidak mendaftarkan user ke Auth Guard bawaan Laravel**, melainkan hanya me-resolve token PASETO dan menyuntikkan (inject) informasi ke dalam `$request->attributes` atau `$request->setUserResolver()`.

### Request (Validation)
Setiap endpoint dengan parameter input (body/query string) WAJIB diproses validasinya menggunakan **FormRequest** (`app/Http/Requests`). Validasi tidak boleh dilakukan di dalam *Controller*.

### Controller
Layer terluar yang paling "tipis" (*Thin Controller*).
**Tugas Controller:**
1. Menerima *Request* dan *FormRequest*.
2. Memanggil metode pada *Service Layer* yang diinjeksi (*Dependency Injection*).
3. Menangkap *Exception* (*try-catch*).
4. Mengembalikan HTTP Response berupa format standar JSON atau kelas *Resource*.

### Service
Layer inti (*Thick Service*).
**Tugas Service:**
1. Menyelesaikan seluruh *Business Logic* (kalkulasi SLA, perhitungan, manipulasi array).
2. Mengeksekusi *Query* ke database melalui *Model*.
3. Mengembalikan bentuk objek murni (`Collection`, `Paginator`, `Array`, atau objek model tunggal) ke *Controller*.

### Model
Layer representasi struktur tabel *database*. 
Dikelompokkan berdasarkan koneksi (Prasojo vs Monitoring). Model di sini sangat ketat mendefinisikan nama tabel, *Primary Key*, dan *Relations*.

### Resource
Menggunakan Laravel *API Resource* (`app/Http/Resources`).
**Tugas Resource:**
Menerjemahkan koleksi Eloquent/Array dari *Service* menjadi susunan JSON murni sesuai kebutuhan UI/UX *Frontend* (menghindari pengeksposan kolom database aslinya).

## 4. Flow Request
Alur satu arah (*Unidirectional Data Flow*):
1. **Client** menembak API.
2. Dicegat oleh **Route** dan dievaluasi oleh **Middleware** (`PasetoAuth`).
3. Diteruskan ke **FormRequest** untuk memastikan tipe data/izin valid.
4. Masuk ke **Controller**. Controller menyiapkan data valid.
5. Memanggil **Service**.
6. **Service** memanggil **Filter** untuk menyusun klausa query secara dinamis, lalu meminta data ke **Model**.
7. **Model** me-return raw data kembali ke **Service**.
8. **Service** melakukan agregasi, penggabungan, atau perhitungan, lalu mengembalikan data ke **Controller**.
9. **Controller** membungkusnya dalam **Resource** atau class pembungkus standard.
10. **Client** menerima JSON.

## 5. Business Logic Pattern
Seluruh proses rumit **dilarang keras** diletakkan di dalam *Controller*. Proses logika bisnis harus dibungkus dalam class *Service*. *Service* biasanya diinjeksi di *constructor Controller*. Metode pada Service bersifat sinkron, dan idealnya bersifat *Stateless*.

## 6. Query Pattern
*   **Filter Pattern:** Pembangkitan query dinamis (seperti filter tanggal, status, ID kecamatan) ditangani oleh kelas `Filter` khusus (misal: `WilayahFilter->apply($query)`).
*   **Anti N+1 (CRITICAL):** Pengambilan data berelasi di dalam perulangan (*loop*) dilarang. Proyek ini mewajibkan penggunaan `with()` untuk *Eager Loading* atau `DB::raw` / *Joins* pada agregasi kompleks (*Count, Avg, Sum*).

## 7. Validation Pattern
Validasi selalu didefinisikan menggunakan **Form Request** bawaan Laravel, bukan memanggil fungsi statis validator. 
Contoh: `public function store(StoreDataRequest $request)`

## 8. Response Pattern
Format JSON response diseragamkan dengan *Helper* `ApiResponse` atau bentuk objek terstruktur, secara umum minimal memiliki kunci-kunci berikut (terutama ketika tidak menggunakan Resource murni):
```json
{
    "status": true,
    "code": 200,
    "message": "Pesan sukses",
    "data": { ... },
    "meta": {
        "page": 1,
        "per_page": 10,
        "total": 100,
        "total_page": 10
    } // opsional
}
```

## 9. Error Handling Pattern
Pola wajib yang digunakan di seluruh *Controller* dan *Service*:
```php
try {
    // Eksekusi kode
} catch (\Throwable $e) {
    Log::error('[NamaClass@namaMethod] ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
    ]);
    throw $e; // Jika di Service
    // ATAU
    return ApiResponse::error('Pesan gagal', 500, ['error' => $e->getMessage()]); // Jika di Controller
}
```

## 10. Logging Pattern
Selalu catat *Error* menggunakan `Log::error`. Pesan wajib memuat referensi kelas dan metodenya secara eksplisit, contohnya: `[DashboardService@getKpi]`. Pada argumen kedua logger (*context*), selalu lampirkan tumpukan pesan error murni berupa array `['trace' => $e->getTraceAsString()]`.

## 11. Naming Convention
*   **Controller:** PascalCase diakhiri kata `Controller` (contoh: `DashboardController.php`).
*   **Service:** PascalCase diakhiri kata `Service` (contoh: `WilayahService.php`).
*   **Model:** PascalCase berbentuk kata benda tunggal (contoh: `SubUser.php`, `Layanan.php`).
*   **Method (Fungsi):** camelCase (contoh: `getKpi`, `buildDistribusiQuery`).
*   **Variabel:** camelCase (contoh: `$totalAjuan`, `$dataDukung`).

## 12. Coding Convention
*   **Strict Typing:** Wajib mencantumkan deklarasi `declare(strict_types=1);` di baris atas setiap file PHP.
*   **Type Hinting & Return Type:** Setiap argumen dan tipe kembalian (terutama di file *Service*) wajib dideklarasikan secara kuat (misal: `public function getDetail(int $id): Model`).
*   Menggunakan format kode *Single Line* untuk *Arrow Function* `fn()` yang sifatnya pendek (biasanya di *Collection map/transform*).

## 13. Git Workflow
Siklus pengerjaan *task*:
1. Kerjakan di percabangan fitur lokal.
2. Gunakan komando terotomatisasi *(Jika di sistem agent)* `/git-branching` untuk membuat *branch* baru dan `/finish-feature` untuk proses otomatisasi commit, push, PR, dan penyatuan.
3. Selalu menggunakan bahasa komit yang spesifik terhadap apa yang dilakukan.

## 14. Branch Strategy
*   **Base Branch:** `staging-amru`
*   Setiap kali hendak mengembangkan fitur baru, **wajib** ditarik (*checkout / branch*) ke percabangan (cabang fitur) dari basis cabang `staging-amru`.
*   Setelah fitur rampung, lakukan proses *Pull Request (Merge)* dan integrasikan kembali ke `staging-amru`.

## 15. Best Practice yang Digunakan
1. Pemisahan model ke dua direktori (*Monitoring* & *Prasojo*) untuk isolasi arsitektur fisik *database*.
2. Penggunaan mekanisme `Cache::remember` untuk *query* statistik berat (misalnya pada fungsi perhitungan KPI Ulasan).
3. Pemusatan modifikasi *Query Filtering* yang dapat dikelola dalam layer khusus (`Filters`).
4. Mengembalikan objek Data Transfer khusus (`API Resources`) ketimbang melempar Collection langsung ke View (menghindari eksposur kolom).

## 16. Hal-Hal yang TIDAK BOLEH Dilakukan
1. ⛔ **Write ke Database Operasional**: DILARANG melakukan aksi DML (Insert, Update, Delete) kepada seluruh entitas tabel bawaan dari aplikasi PRASOJO lama (`mysql_prasojo`).
2. ⛔ **Mengubah Struktur Tabel Prasojo**: DILARANG membuat file migrasi (*Migration*) yang berusaha mengubah tipe kolom, menambah, maupun menghapus (*drop*) kolom dari database tabel operasional (*prasojo*). Migrasi penambahan indeks diperbolehkan.
3. ⛔ **Melakukan Auth->id()**: Karena aplikasi tidak bergantung pada *Auth Guard Web/Sanctum* melainkan Token kustom, penggunaan *helper* `auth()->id()` akan me-*return null*. Segala penarikan identitas pengguna dilakukan melalui request (`$request->user()` atau `$request->attributes->get('auth_user_id')`).
4. ⛔ **Menaruh Kode Bisnis di Controller**: Segala bentuk perulangan, perhitungan, agregasi array dilarang berada di dalam struktur controller. Controller murni untuk mengatur keluar-masuknya HTTP Request.
5. ⛔ **Mengabaikan Model Konfigurasi Koneksi**: Setiap model dari `mysql_prasojo` dilarang menghilangkan inisialisasi wajib di *Class*-nya, yaitu:
    ```php
    protected $connection = 'mysql_prasojo';
    public $timestamps = false;
    ```
6. ⛔ **N+1 Query Relasi Berulang**: DILARANG keras memanggil/mengeksekusi relasi dari sebuah model pada sebuah *array iterasi (looping)*, yang berdampak merusak performa *database*. Wajib menggunakan `with()` untuk memuat secara bersamaan.
