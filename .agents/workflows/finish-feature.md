---
name: finish-feature
description: Otomatisasi fetch, commit, push, pull request, dan merge ke branch staging-amru.
---

# Workflow: Finish Feature

Workflow ini digunakan untuk menyelesaikan pekerjaan pada branch fitur dan menggabungkannya ke branch `staging-amru`. Pastikan agen menjalankan langkah-langkah ini secara sistematis.

## Langkah-langkah Eksekusi

### 1. Sinkronisasi Awal (Fetch)
- **Fetch Origin:** Jalankan perintah `git fetch origin` untuk memperbarui informasi state repository dari remote agar selalu mendapatkan perubahan terbaru.

### 2. Commit Perubahan
- **Status & Stage:** Periksa perubahan dengan `git status`, kemudian jalankan `git add .` (atau spesifik file).
- **Commit:** Jalankan `git commit -m "<Pesan Commit>"` menggunakan standar Conventional Commits (misalnya: `feat: implement dashboard kpi endpoints`).

### 3. Push Branch Fitur
- **Push ke Remote:** Jalankan `git push origin HEAD` (atau `git push -u origin <nama-branch>`).

### 4. Pull Request ke `staging-amru`
- **Buat PR:** Gunakan GitHub CLI (`gh pr create --base staging-amru --head <nama-branch> --title "feat: <judul>" --body "<deskripsi>"`) atau buat Pull Request melalui antarmuka web, tergantung preferensi pengguna.

### 5. Merge ke `staging-amru`
- **Checkout & Pull:** Berpindah ke branch target (`git checkout staging-amru`) lalu pastikan terbaru (`git pull origin staging-amru`).
- **Merge:** Gabungkan branch fitur ke staging (`git merge <nama-branch>`).
- **Push Staging:** Jika tidak ada konflik (atau konflik sudah diselesaikan), kirim perubahan ke remote (`git push origin staging-amru`).
