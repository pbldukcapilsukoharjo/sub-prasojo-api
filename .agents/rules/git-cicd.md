# Aturan Git & CI/CD — Sub Prasojo API

## Branch Strategy
| Branch | Fungsi | Push Langsung |
|--------|--------|---------------|
| `main` | Production | ❌ DILARANG (ada protection.yml) |
| `staging` | Staging/QA | ❌ DILARANG (ada protection.yml) |
| `staging-amru` | Staging Dev (Parent) | ❌ DILARANG |
| `feature/*` | Development | ✅ Boleh |
| `fix/*` | Bugfix | ✅ Boleh |

**Alur Kerja Utama Agen (Wajib):**
Saat mengeksekusi task/fitur baru, agen **TIDAK BOLEH** langsung menulis kode di branch utama/parent. Agen WAJIB melakukan:
1. Pastikan checkout dan update dari *parent branch* yaitu `staging-amru`.
2. Buat branch baru untuk fitur tersebut (contoh: `git checkout -b feature/<nama-task> staging-amru`).
3. Tulis kode, tes, lalu commit (Conventional Commits) di branch feature tersebut.
4. Buat Pull Request (atau informasikan user) untuk review/merge.

## Conventional Commits (WAJIB)
```
feat: tambah endpoint dashboard KPI
fix: perbaiki filter tanggal pada pengajuan
refactor: pindahkan logic ke DashboardService
test: tambah feature test untuk modul SLA
docs: update api documentation prefix v1
chore: update dependency maatwebsite/excel
ci: perbaiki workflow CI untuk PHP 8.2
```

## CI Pipeline (Otomatis via GitHub Actions)
Setiap PR ke `main` atau `staging` akan menjalankan:
1. Setup PHP 8.2 + extensions (mbstring, xml, pdo_mysql, zip)
2. `composer install`
3. `php artisan key:generate`
4. `php artisan migrate --force` (SQLite untuk CI)
5. `php artisan test`
6. Build & push Docker image ke DockerHub
7. Notifikasi Telegram (sukses/gagal)

## Aturan PR
- Pastikan semua test PASS sebelum merge
- Jika menambah endpoint baru, pastikan Feature Test sudah dibuat
- Jika mengubah response format, update `docs/api_documentation.md`

## Environment Secrets (GitHub)
Secrets yang diperlukan di repository:
- `DOCKERHUB_USERNAME` — Username DockerHub
- `DOCKERHUB_TOKEN` — Access token DockerHub
- `TELEGRAM_CHAT_ID` — Chat ID untuk notifikasi
- `TELEGRAM_BOT_TOKEN` — Bot token Telegram
