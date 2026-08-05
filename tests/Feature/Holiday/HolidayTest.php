<?php

namespace Tests\Feature\Holiday;

use App\Models\MasterLiburNasional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TestHolidayArrayExport implements FromArray
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }
}

class HolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_holidays()
    {
        $response = $this->getJson('/api/v1/holidays');
        $response->assertStatus(401);
    }

    public function test_index_holidays_returns_paginated_list()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2026-01-01',
            'keterangan' => 'Tahun Baru Masehi',
        ]);

        MasterLiburNasional::create([
            'tanggal' => '2026-08-17',
            'keterangan' => 'Hari Kemerdekaan RI',
        ]);

        $response = $this->getJson('/api/v1/holidays');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'tanggal', 'keterangan', 'created_at', 'updated_at'],
                ],
                'meta' => ['page', 'per_page', 'total', 'total_page'],
            ]);

        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_index_holidays_filters_by_tahun()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2025-12-25',
            'keterangan' => 'Hari Natal 2025',
        ]);

        MasterLiburNasional::create([
            'tanggal' => '2026-01-01',
            'keterangan' => 'Tahun Baru 2026',
        ]);

        $response = $this->getJson('/api/v1/holidays?tahun=2026');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertStringContainsString('2026', $response->json('data.0.tanggal'));
    }

    public function test_index_holidays_filters_by_search_keterangan()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2026-01-01',
            'keterangan' => 'Tahun Baru Masehi',
        ]);

        MasterLiburNasional::create([
            'tanggal' => '2026-08-17',
            'keterangan' => 'Hari Kemerdekaan RI',
        ]);

        $response = $this->getJson('/api/v1/holidays?search=Kemerdekaan');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Hari Kemerdekaan RI', $response->json('data.0.keterangan'));
    }

    public function test_store_single_holiday_successfully()
    {
        $this->authenticateWithPaseto();

        $payload = [
            'holidays' => [
                [
                    'tanggal' => '2027-01-01',
                    'keterangan' => 'Tahun Baru 2027',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/holidays', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'code' => 201,
                'status' => true,
            ]);

        $this->assertDatabaseHas('master_libur_nasionals', [
            'tanggal' => '2027-01-01',
            'keterangan' => 'Tahun Baru 2027',
        ]);
    }

    public function test_store_bulk_holidays_successfully()
    {
        $this->authenticateWithPaseto();

        $payload = [
            'holidays' => [
                ['tanggal' => '2027-05-01', 'keterangan' => 'Hari Buruh International'],
                ['tanggal' => '2027-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ],
        ];

        $response = $this->postJson('/api/v1/holidays', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('master_libur_nasionals', ['keterangan' => 'Hari Buruh International']);
        $this->assertDatabaseHas('master_libur_nasionals', ['keterangan' => 'Hari Lahir Pancasila']);
    }

    public function test_store_fails_when_tanggal_already_exists_in_db()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2027-01-01',
            'keterangan' => 'Existing Holiday',
        ]);

        $payload = [
            'holidays' => [
                ['tanggal' => '2027-01-01', 'keterangan' => 'Duplicate Holiday'],
            ],
        ];

        $response = $this->postJson('/api/v1/holidays', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'status' => false,
            ]);
    }

    public function test_store_fails_when_duplicate_tanggal_in_request_body()
    {
        $this->authenticateWithPaseto();

        $payload = [
            'holidays' => [
                ['tanggal' => '2027-02-01', 'keterangan' => 'Libur A'],
                ['tanggal' => '2027-02-01', 'keterangan' => 'Libur B'],
            ],
        ];

        $response = $this->postJson('/api/v1/holidays', $payload);

        $response->assertStatus(400);
    }


    public function test_update_holiday_successfully()
    {
        $this->authenticateWithPaseto();

        $holiday = MasterLiburNasional::create([
            'tanggal' => '2026-04-10',
            'keterangan' => 'Wafat Isa Almasih',
        ]);

        $payload = [
            'tanggal' => '2026-04-10',
            'keterangan' => 'Wafat Yesus Kristus',
        ];

        $response = $this->putJson("/api/v1/holidays/{$holiday->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'data' => [
                    'keterangan' => 'Wafat Yesus Kristus',
                ],
            ]);

        $this->assertDatabaseHas('master_libur_nasionals', [
            'id' => $holiday->id,
            'keterangan' => 'Wafat Yesus Kristus',
        ]);
    }

    public function test_update_holiday_fails_when_tanggal_duplicate()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2026-05-01',
            'keterangan' => 'Hari Buruh',
        ]);

        $holiday2 = MasterLiburNasional::create([
            'tanggal' => '2026-05-02',
            'keterangan' => 'Libur Dummy',
        ]);

        $payload = [
            'tanggal' => '2026-05-01', // duplicate with first holiday
            'keterangan' => 'Ubah Ke Tanggal Buruh',
        ];

        $response = $this->putJson("/api/v1/holidays/{$holiday2->id}", $payload);

        $response->assertStatus(400);
    }

    public function test_delete_single_holiday_successfully()
    {
        $this->authenticateWithPaseto();

        $holiday = MasterLiburNasional::create([
            'tanggal' => '2026-12-25',
            'keterangan' => 'Hari Natal',
        ]);

        $response = $this->deleteJson("/api/v1/holidays/{$holiday->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('master_libur_nasionals', [
            'id' => $holiday->id,
        ]);
    }

    public function test_delete_bulk_holidays_successfully()
    {
        $this->authenticateWithPaseto();

        $h1 = MasterLiburNasional::create(['tanggal' => '2026-11-01', 'keterangan' => 'Libur 1']);
        $h2 = MasterLiburNasional::create(['tanggal' => '2026-11-02', 'keterangan' => 'Libur 2']);

        $payload = [
            'ids' => [$h1->id, $h2->id],
        ];

        $response = $this->deleteJson('/api/v1/holidays/bulk', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('master_libur_nasionals', ['id' => $h1->id]);
        $this->assertDatabaseMissing('master_libur_nasionals', ['id' => $h2->id]);
    }

    public function test_download_template_returns_excel_file()
    {
        $this->authenticateWithPaseto();

        $response = $this->get('/api/v1/holidays/template');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_excel_successfully()
    {
        $this->authenticateWithPaseto();

        $content = Excel::raw(new TestHolidayArrayExport([
            ['Tanggal (YYYY-MM-DD)', 'Keterangan'],
            ['2028-01-01', 'Tahun Baru 2028'],
            ['2028-08-17', 'Kemerdekaan 2028'],
        ]), \Maatwebsite\Excel\Excel::XLSX);

        $file = UploadedFile::fake()->createWithContent('holidays.xlsx', $content);

        $response = $this->postJson('/api/v1/holidays/import', [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'code' => 201,
                'status' => true,
                'data' => [
                    'total_imported' => 2,
                ],
            ]);

        $this->assertDatabaseHas('master_libur_nasionals', ['tanggal' => '2028-01-01']);
        $this->assertDatabaseHas('master_libur_nasionals', ['tanggal' => '2028-08-17']);
    }

    public function test_import_excel_fails_due_to_internal_duplicate_dates()
    {
        $this->authenticateWithPaseto();

        $content = Excel::raw(new TestHolidayArrayExport([
            ['Tanggal (YYYY-MM-DD)', 'Keterangan'],
            ['2028-05-01', 'Hari Buruh'],
            ['2028-05-01', 'Hari Buruh Duplikat'],
        ]), \Maatwebsite\Excel\Excel::XLSX);

        $file = UploadedFile::fake()->createWithContent('holidays_dup.xlsx', $content);

        $response = $this->postJson('/api/v1/holidays/import', [
            'file' => $file,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'status' => false,
            ]);

        $this->assertDatabaseMissing('master_libur_nasionals', ['tanggal' => '2028-05-01']);
    }

    public function test_import_excel_fails_due_to_db_duplicate_dates_and_rollbacks()
    {
        $this->authenticateWithPaseto();

        MasterLiburNasional::create([
            'tanggal' => '2028-12-25',
            'keterangan' => 'Existing Natal 2028',
        ]);

        $content = Excel::raw(new TestHolidayArrayExport([
            ['Tanggal (YYYY-MM-DD)', 'Keterangan'],
            ['2028-12-24', 'Malam Natal 2028'],
            ['2028-12-25', 'Hari Natal 2028 (Duplikat DB)'],
        ]), \Maatwebsite\Excel\Excel::XLSX);

        $file = UploadedFile::fake()->createWithContent('holidays_db_dup.xlsx', $content);

        $response = $this->postJson('/api/v1/holidays/import', [
            'file' => $file,
        ]);

        $response->assertStatus(400);

        // Ensure full rollback — '2028-12-24' should NOT be inserted into DB
        $this->assertDatabaseMissing('master_libur_nasionals', ['tanggal' => '2028-12-24']);
    }

    public function test_import_excel_fails_for_invalid_file_format()
    {
        $this->authenticateWithPaseto();

        $file = UploadedFile::fake()->create('invalid.txt', 100, 'text/plain');

        $response = $this->postJson('/api/v1/holidays/import', [
            'file' => $file,
        ]);

        $response->assertStatus(400);
    }
}
