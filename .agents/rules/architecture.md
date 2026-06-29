# Aturan Arsitektur — Sub Prasojo API

## Service Pattern (WAJIB)
Semua business logic WAJIB ditempatkan di Service layer. Controller HANYA menerima request dan mengembalikan response.

```
Request → Controller → Service → Response
                         ↓
                    Filter (query filtering)
                    Enum (konstanta status)
```

## Struktur Direktori
```
app/
├── Enums/            → Konstanta status (AjuanStatus, dll)
├── Exports/          → Class export maatwebsite/excel
├── Filters/          → Class filter query per modul
├── Http/
│   ├── Controllers/  → Hanya orchestrate request/response
│   ├── Middleware/    → PasetoAuth, dll
│   ├── Requests/     → Form Request Validation
│   └── Responses/    → ApiResponse helper
├── Models/           → Eloquent models
└── Services/         → Business logic (CORE)
```

## Konvensi Penamaan
| Komponen | Format | Contoh |
|----------|--------|--------|
| Controller | `{Modul}Controller.php` | `DashboardController.php` |
| Service | `{Modul}Service.php` | `DashboardService.php` |
| Filter | `{Modul}Filter.php` | `DashboardFilter.php` |
| Export | `{Modul}{Aksi}Export.php` | `OperatorRankingExport.php` |
| Request | `{Aksi}{Modul}Request.php` | `UpdateProfileRequest.php` |
| Feature Test | `tests/Feature/{Modul}/{Modul}{Aksi}Test.php` | `DashboardKpiTest.php` |
| Unit Test | `tests/Unit/Services/{Modul}ServiceTest.php` | `DashboardServiceTest.php` |

## Anti-Pattern yang DILARANG
- ❌ Business logic di dalam Controller
- ❌ Query builder langsung di Controller
- ❌ Hardcode string status (gunakan Enum)
- ❌ Response JSON manual (gunakan `ApiResponse`)
- ❌ Manipulasi data operasional (sistem ini READ-ONLY)
