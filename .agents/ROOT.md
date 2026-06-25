# Sub Prasojo API — Agent Instructions

## Tentang Proyek
Sistem Monitoring Layanan Disdukcapil Kabupaten Sukoharjo (Dashboard Eksekutif).
Backend API yang bersifat **READ-ONLY** untuk menyajikan data operasional kependudukan ke dashboard monitoring.

## Stack Teknologi
- **Framework:** Laravel 12 (PHP 8.2+)
- **Auth:** PASETO via `paragonie/paseto`
- **Database:** MariaDB 10.4+ (dual connection)
- **Cache:** Redis
- **Export:** `maatwebsite/excel`
- **CI/CD:** GitHub Actions → DockerHub → Telegram notification

## Dokumen Referensi (WAJIB DIBACA)

### Spesifikasi Produk
| Dokumen | Path | Isi |
|---------|------|-----|
| PRD | `docs/PRD.md` | Arsitektur, standar response, spesifikasi endpoint |
| API Documentation | `docs/api_documentation.md` | Detail request/response per endpoint |
| Database Schema | `docs/database_schema.md` | Struktur tabel database operasional (prasojo) |
| Roadmap | `docs/roadmap.md` | Fase pengembangan & checklist progress |

### Aturan Agent
| File | Isi |
|------|-----|
| `.agents/rules/architecture.md` | Service Pattern, struktur direktori, naming convention |
| `.agents/rules/api-response.md` | Format response JSON, bahasa Indonesia |
| `.agents/rules/database.md` | Dual connection, read-only, model convention |
| `.agents/rules/git-cicd.md` | Branch strategy, conventional commits, CI pipeline |
| `.agents/rules/testing.md` | Feature test & unit test requirements |

### Rencana Eksekusi
| File | Isi |
|------|-----|
| `.agents/implementation_plan.md` | Detail teknis per fase dari roadmap |
| `.agents/task.md` | Checklist granular yang bisa di-execute |

## Cara Kerja Agent
1. **SELALU baca rules** di `.agents/rules/` sebelum membuat perubahan
2. **Cek task.md** untuk mengetahui item yang sedang dikerjakan
3. **Persiapan Branch:** Sebelum memulai implementasi fitur/task, **WAJIB** membuat *feature branch* baru dari parent branch `staging-amru` (contoh: `git checkout -b feature/<nama-task> staging-amru`).
4. **Ikuti implementation_plan.md** untuk detail teknis setiap task
5. **Update task.md** setelah menyelesaikan item (mark `[x]`)
6. **Jalankan test** setelah setiap perubahan: `php artisan test`
7. **Commit** dengan format conventional commits
8. **Buat Log Eksekusi** di `.agents/logs/` setelah menyelesaikan batch pekerjaan (lihat `.agents/rules/agent-logging.md`)

## Peringatan Penting
- ⚠️ Database operasional bersifat **READ-ONLY** — jangan pernah modify data
- ⚠️ Semua pesan API **WAJIB Bahasa Indonesia**
- ⚠️ Semua route menggunakan prefix **`/api/v1/`**
- ⚠️ Jangan push langsung ke `main` atau `staging`
