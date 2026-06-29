---
trigger: always_on
---

# Aturan Database — Sub Prasojo API

## Dual Connection
Proyek ini menggunakan 2 koneksi database yang WAJIB dibedakan:

| Connection | Database | Sifat | Tabel |
|-----------|----------|-------|-------|
| `mysql` (default) | Dashboard | Read-Write | `sub_users`, `refresh_tokens` |
| `mysql_prasojo` | Operasional Lama | **READ-ONLY** | `ajuan`, `admin`, `ajuan_review`, `layanan`, `lembar_kerja`, `produk`, dll |

## Model untuk Tabel Operasional (Lama)
Semua model yang membaca tabel dari `mysql_prasojo` HARUS menyertakan:

```php
class Ajuan extends Model
{
    protected $connection = 'mysql_prasojo';
    protected $table = 'ajuan';
    protected $primaryKey = 'ajuan_id';
    public $timestamps = false;  // kolom timestamp tidak standar Laravel
}
```

**Alasan `$timestamps = false`:** Tabel lama menggunakan kolom `create_datetime` / `update_datetime`, BUKAN `created_at` / `updated_at`.

## Migration
- ✅ BOLEH membuat migration untuk tabel di database dashboard
- ✅ BOLEH membuat migration untuk menambah INDEX pada tabel prasojo
- ❌ DILARANG membuat migration yang mengubah STRUKTUR tabel prasojo
- ❌ DILARANG melakukan INSERT/UPDATE/DELETE pada data operasional

## Index yang Diperlukan (Tabel `ajuan`)
```sql
-- Kolom yang WAJIB di-index:
ajuan_status
ajuan_create_datetime
ajuan_update_datetime
ajuan_no_reg
ajuan_kecamatan_code
ajuan_is_online
ajuan_pelapor_role_name
ajuan_pelapor_id
```

## Format Tanggal
| Layer | Format |
|-------|--------|
| Input API | `dd-mm-yyyy` |
| Database MariaDB | `datetime` standard |
| Konversi | Di Filter layer via `Carbon::createFromFormat('d-m-Y', $value)` |

## Mencegah N+1 Query (CRITICAL)
N+1 query **DILARANG KERAS** karena dapat menyebabkan beban fatal pada server, terutama saat melakukan aggregasi atau mengambil *list* data yang cukup besar.
- **Eager Loading:** Gunakan `with()` atau `loadMissing()` pada setiap relasi Eloquent yang akan dipanggil di dalam loop atau di-*serialize* sebagai JSON (misalnya relasi `pelapor` pada `Ajuan`).
- **Validasi Kinerja:** Sebelum membuat Pull Request atau menyelesaikan task, pastikan tidak ada query relasional yang dieksekusi berulang kali di dalam loop. Gunakan `DB::raw` atau query *join* jika pengambilan data relasional terlalu kompleks dan butuh perhitungan statistik (aggregasi).
