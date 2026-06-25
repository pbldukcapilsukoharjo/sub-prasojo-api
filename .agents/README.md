# 🤖 Entry Point Agen AI — Sub Prasojo API

Dokumen ini adalah **titik masuk utama (entry point)** untuk agen AI. Sebelum mengerjakan tugas apapun, **BACA DOKUMEN INI TERLEBIH DAHULU**. Dokumen ini memetakan setiap jenis pekerjaan ke dokumen referensi, aturan, dan workflow yang **wajib** diikuti.

## 📌 1. Pemetaan Tugas ke Dokumen Referensi

Setiap kali menerima tugas dari pengguna, identifikasi jenis tugasnya dan baca dokumen yang relevan:

| Jenis Tugas / Kebutuhan | Dokumen Referensi yang WAJIB Dibaca |
|-------------------------|-------------------------------------|
| **Menambah Fitur Baru** | `.agents/workflows/add-feature.md` (Alur kerja utama), `docs/PRD.md`, `docs/api_documentation.md` |
| **Arsitektur & Koding** | `.agents/rules/architecture.md`, `.agents/rules/api-response.md` |
| **Akses & Query Data**  | `.agents/rules/database.md`, `docs/database_schema.md` |
| **Pengujian (Testing)** | `.agents/rules/testing.md` |
| **Alur Git & Branching**| `.agents/workflows/git-branching.md`, `.agents/rules/git-cicd.md` |
| **Melihat Rencana**     | `docs/roadmap.md`, `.agents/implementation_plan.md`, `.agents/task.md` |

## ⚙️ 2. Standar Operasional Agen (SOP)

1. **Persiapan Branch:** Sebelum mengedit/menambah kode fitur, **WAJIB** membuat *feature branch* baru dari parent branch `staging-amru` (lihat `git-branching.md`).
2. **Cek Rencana:** Baca `implementation_plan.md` dan `task.md` untuk mengetahui konteks teknis item yang akan dikerjakan.
3. **Standar Kode:** Patuhi `architecture.md` (Service Pattern) dan `api-response.md` (respons Bahasa Indonesia via ApiResponse helper).
4. **Database Read-Only:** Database operasional bersifat `READ-ONLY`. Jangan pernah melakukan operasi `INSERT/UPDATE/DELETE` ke tabel yang sudah ada (lihat `database.md`).
5. **Testing:** Jalankan `php artisan test` setiap selesai mengubah logika.
6. **Logging:** Buat *Log Eksekusi* di direktori `.agents/logs/` setelah menyelesaikan batch pekerjaan.
7. **Checklist:** Update `.agents/task.md` (tandai `[x]`) dan beritahu pengguna jika sudah siap commit/Pull Request.

## 🛠 3. Stack Teknologi Proyek
- **Framework:** Laravel 12 (PHP 8.2+)
- **Auth:** PASETO via `paragonie/paseto`
- **Database:** MariaDB 10.4+ (Dual connection, MySQL default & MySQL Prasojo)
- **CI/CD:** GitHub Actions (otomatis test & build saat push ke branch tertentu)
