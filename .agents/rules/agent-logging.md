# Aturan Logging Eksekusi Agent — Sub Prasojo API

Untuk menjaga rekam jejak apa saja yang sudah dilakukan oleh agent pada proyek ini, setiap kali agent menyelesaikan suatu batch pekerjaan (misalnya mengimplementasikan satu fase, memperbaiki sekumpulan bug, atau melakukan refactor), agent **WAJIB** membuat file log recap.

## Lokasi dan Format Nama File
- **Lokasi:** `.agents/logs/`
- **Format Nama:** `YYYYMMDD_HHMMSS_recap.md` (contoh: `20260625_143000_recap.md`)

## Struktur File Log
Setiap file log wajib memuat struktur markdown berikut:

```markdown
# Execution Recap - [Tanggal & Waktu]

## 🎯 Objektif / Konteks
*(Sebutkan secara singkat instruksi apa yang diberikan atau apa tujuan eksekusi kali ini)*

## ✅ Task yang Diselesaikan
*(Daftarkan task dari `task.md` yang telah ditandai selesai pada sesi ini)*
- [x] Task A
- [x] Task B

## 📂 File yang Diubah / Dibuat
*(Daftarkan file yang mengalami perubahan)*
- `app/Services/DashboardService.php` (Dibuat baru)
- `routes/api.php` (Menambahkan route dashboard)

## 🛠️ Command yang Dijalankan
*(Daftar command terminal yang dijalankan, misalnya artisan test atau composer)*
- `php artisan test --filter=DashboardKpiTest` (PASS)

## ⚠️ Isu / Catatan Penting
*(Catat jika ada kendala, penyesuaian dari rencana awal, log error, atau TODO yang belum tuntas untuk next session)*
```

## Kapan Log Dibuat?
- Tepat **sebelum** agent menyelesaikan sesinya atau setelah sebuah task / fase selesai dikerjakan.
