---
name: git-branching
description: Otomatisasi pembuatan branch baru dari staging-amru saat eksekusi task fitur
---

# Git Branching Workflow

Workflow ini dijalankan otomatis oleh agen **sebelum** mulai menulis atau memodifikasi kode untuk sebuah task atau fitur baru.

## Parent Branch
- **staging-amru**

## Workflow:
1. Pindah ke parent branch dan pastikan update:
   - Run `git checkout staging-amru`
   - Run `git pull origin staging-amru` (opsional, jika terhubung ke remote)

2. Buat branch fitur baru:
   - Tentukan nama branch berdasarkan fitur yang dikerjakan dengan akhiran `-byamru`: `feature/<nama-task>-byamru`
   - Run `git checkout -b feature/<nama-task>-byamru`

3. Lanjutkan Eksekusi:
   - Mulai implementasi kode di branch baru.
   - Lakukan commit dengan Conventional Commits.
