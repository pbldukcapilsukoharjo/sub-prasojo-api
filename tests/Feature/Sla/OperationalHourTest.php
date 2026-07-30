<?php

namespace Tests\Feature\Sla;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\MasterJamOperasional;

class OperationalHourTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GET /api/v1/operational-hours when DB is empty triggers auto-seeding.
     */
    public function test_get_operational_hours_empty_db_triggers_seeding()
    {
        // Pastikan tabel kosong
        MasterJamOperasional::truncate();
        $this->assertEquals(0, MasterJamOperasional::count());

        $this->authenticateWithPaseto();

        $response = $this->getJson('/api/v1/operational-hours');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'id',
                             'hari_kode',
                             'hari_nama',
                             'jam_buka',
                             'jam_tutup',
                             'is_libur',
                         ]
                     ]
                 ]);

        // Harus terisi 7 hari
        $this->assertEquals(7, MasterJamOperasional::count());
        $response->assertJsonCount(7, 'data');
    }

    /**
     * Test GET /api/v1/operational-hours when DB already has data.
     */
    public function test_get_operational_hours_success()
    {
        // Buat data tiruan
        MasterJamOperasional::create([
            'hari_kode' => 1,
            'hari_nama' => 'Senin',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '15:00:00',
            'is_libur' => false
        ]);

        $this->authenticateWithPaseto();

        $response = $this->getJson('/api/v1/operational-hours');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'hari_nama' => 'Senin',
                     'jam_buka' => '08:00:00',
                     'jam_tutup' => '15:00:00',
                     'is_libur' => false
                 ]);
    }

    /**
     * Test PUT /api/v1/operational-hours/{id} with normal parameters.
     */
    public function test_update_operational_hours_success()
    {
        $hour = MasterJamOperasional::create([
            'hari_kode' => 1,
            'hari_nama' => 'Senin',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '15:00:00',
            'is_libur' => false
        ]);

        $this->authenticateWithPaseto();

        $response = $this->putJson("/api/v1/operational-hours/{$hour->id}", [
            'jam_buka' => '09:00:00',
            'jam_tutup' => '17:00:00',
            'is_libur' => false
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.jam_buka', '09:00:00')
                 ->assertJsonPath('data.jam_tutup', '17:00:00')
                 ->assertJsonPath('data.is_libur', false);

        $this->assertDatabaseHas('master_jam_operasional', [
            'id' => $hour->id,
            'jam_buka' => '09:00:00',
            'jam_tutup' => '17:00:00',
            'is_libur' => false
        ]);
    }

    /**
     * Test normalization logic for time (HH:MM -> HH:MM:00) and string booleans ("false"/"true").
     */
    public function test_update_operational_hours_normalization()
    {
        $hour = MasterJamOperasional::create([
            'hari_kode' => 1,
            'hari_nama' => 'Senin',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '15:00:00',
            'is_libur' => false
        ]);

        $this->authenticateWithPaseto();

        // Mengirim jam format HH:MM dan string "false"
        $response = $this->putJson("/api/v1/operational-hours/{$hour->id}", [
            'jam_buka' => '09:00',
            'jam_tutup' => '16:30',
            'is_libur' => 'false'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.jam_buka', '09:00:00')
                 ->assertJsonPath('data.jam_tutup', '16:30:00')
                 ->assertJsonPath('data.is_libur', false);

        $this->assertDatabaseHas('master_jam_operasional', [
            'id' => $hour->id,
            'jam_buka' => '09:00:00',
            'jam_tutup' => '16:30:00',
            'is_libur' => false
        ]);
    }

    /**
     * Test that setting is_libur to true automatically nullifies jam_buka and jam_tutup.
     */
    public function test_update_operational_hours_libur_nullifies_hours()
    {
        $hour = MasterJamOperasional::create([
            'hari_kode' => 1,
            'hari_nama' => 'Senin',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '15:00:00',
            'is_libur' => false
        ]);

        $this->authenticateWithPaseto();

        $response = $this->putJson("/api/v1/operational-hours/{$hour->id}", [
            'jam_buka' => '09:00:00',
            'jam_tutup' => '16:00:00',
            'is_libur' => 'true' // Di-set libur
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.jam_buka', null)
                 ->assertJsonPath('data.jam_tutup', null)
                 ->assertJsonPath('data.is_libur', true);

        $this->assertDatabaseHas('master_jam_operasional', [
            'id' => $hour->id,
            'jam_buka' => null,
            'jam_tutup' => null,
            'is_libur' => true
        ]);
    }

    /**
     * Test validation fails when is_libur is false but hours are not provided.
     */
    public function test_update_operational_hours_validation_errors()
    {
        $hour = MasterJamOperasional::create([
            'hari_kode' => 1,
            'hari_nama' => 'Senin',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '15:00:00',
            'is_libur' => false
        ]);

        $this->authenticateWithPaseto();

        // Omit jam_buka and jam_tutup when is_libur = false
        $response = $this->putJson("/api/v1/operational-hours/{$hour->id}", [
            'is_libur' => false
        ]);

        $response->assertStatus(400)
                 ->assertJsonValidationErrors(['jam_buka', 'jam_tutup'], 'data');
    }
}
