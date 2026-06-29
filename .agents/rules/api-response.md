# Aturan Response API — Sub Prasojo API

## Bahasa
Semua pesan API (`message`) **MUTLAK menggunakan Bahasa Indonesia**. Tanpa pengecualian.

## Format Response

### Sukses (2xx) — Dengan Paginasi
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data",
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 145,
    "total_page": 15
  }
}
```

### Sukses (2xx) — Tanpa Paginasi
```json
{
  "status": true,
  "code": 200,
  "message": "Berhasil mengambil data profil",
  "data": {}
}
```
Field `meta` HANYA pada endpoint dengan paginasi. Untuk endpoint tunggal, `data` berupa Object `{}`.

### Error (4xx & 5xx)
```json
{
  "status": false,
  "code": 404,
  "message": "Data tidak ditemukan",
  "data": null
}
```

### Validation Error (400)
```json
{
  "status": false,
  "code": 400,
  "message": "Validasi gagal. Silakan periksa kembali input Anda.",
  "data": {
    "nama_field": ["Pesan error spesifik (Bahasa Indonesia)"]
  }
}
```

### Unauthorized (401)
```json
{
  "status": false,
  "code": 401,
  "message": "Akses ditolak. Token PASETO tidak valid atau kedaluwarsa.",
  "data": null
}
```

## Helper Class
Gunakan `App\Http\Responses\ApiResponse` untuk SEMUA response. JANGAN membuat response JSON manual.

```php
// ✅ BENAR
return ApiResponse::success('Berhasil mengambil data', $data);
return ApiResponse::paginated('Berhasil mengambil data', $paginator);
return ApiResponse::error('Data tidak ditemukan', 404);

// ❌ SALAH
return response()->json(['status' => true, 'data' => $data]);
```
