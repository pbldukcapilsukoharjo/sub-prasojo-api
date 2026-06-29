# Sub Prasojo API

Backend API layanan administrasi berbasis Laravel 12 untuk pengelolaan antrean dokumen, lembar kerja, dan proses layanan PADUKA/TAMAT.

---

# Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL / MariaDB
- Laravel Eloquent ORM
- Service Layer Architecture
- Resource API Response
- Request Validation Layer

---

# Architecture

Project ini menggunakan clean architecture sederhana:

```text
Route
→ Controller
→ Request Validation
→ Service
→ Filter
→ Model
→ Resource
→ JSON Response
