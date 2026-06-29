# Aturan Testing — Sub Prasojo API

## Wajib Testing
Setiap modul WAJIB memiliki:
- **Feature Test** — menguji HTTP request/response secara end-to-end
- **Unit Test** — menguji Service layer secara terisolasi

## Feature Test (tests/Feature/)
Setiap Feature Test HARUS menguji minimal:
1. ✅ Response sukses dengan format yang benar (`status`, `code`, `message`, `data`)
2. ✅ Response 401 Unauthorized tanpa token
3. ✅ Validasi parameter filter (jika ada)
4. ✅ Paginasi `meta` field (jika endpoint mendukung paginasi)

### Contoh Struktur
```php
class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_returns_correct_format(): void
    {
        // Arrange: buat user & login → dapatkan token
        // Act: GET /api/v1/dashboard/kpi dengan token
        // Assert: status 200, format sesuai API docs
    }

    public function test_kpi_returns_401_without_token(): void
    {
        // Act: GET /api/v1/dashboard/kpi TANPA token
        // Assert: status 401
    }

    public function test_kpi_filters_by_kecamatan(): void
    {
        // Arrange: data berbeda per kecamatan
        // Act: GET dengan ?id_kecamatan=X
        // Assert: data terfilter sesuai kecamatan
    }
}
```

## Unit Test (tests/Unit/Services/)
Unit Test fokus pada business logic di Service tanpa HTTP layer:

```php
class DashboardServiceTest extends TestCase
{
    public function test_get_kpi_calculates_correctly(): void
    {
        // Mock data atau gunakan factory
        // Panggil service method langsung
        // Assert hasil kalkulasi benar
    }
}
```

## Running Tests
```bash
# Semua test
php artisan test

# Hanya Feature Test
php artisan test --testsuite=Feature

# Modul spesifik
php artisan test --filter=DashboardKpiTest

# Dengan coverage (jika diperlukan)
php artisan test --coverage
```

## CI Integration
Test dijalankan otomatis pada setiap PR via GitHub Actions:
- Database: SQLite (in-memory untuk kecepatan)
- Environment: `.env.example` → `.env`
- Command: `php artisan test`
