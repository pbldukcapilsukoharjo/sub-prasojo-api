<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlaConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Jam Operasional
        DB::table('master_jam_operasional')->delete();

        $jamOperasional = [
            ['hari_kode' => 1, 'hari_nama' => 'Senin', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
            ['hari_kode' => 2, 'hari_nama' => 'Selasa', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
            ['hari_kode' => 3, 'hari_nama' => 'Rabu', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
            ['hari_kode' => 4, 'hari_nama' => 'Kamis', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
            ['hari_kode' => 5, 'hari_nama' => 'Jumat', 'jam_buka' => '08:00:00', 'jam_tutup' => '13:00:00', 'is_libur' => false],
            ['hari_kode' => 6, 'hari_nama' => 'Sabtu', 'jam_buka' => null, 'jam_tutup' => null, 'is_libur' => true],
            ['hari_kode' => 7, 'hari_nama' => 'Minggu', 'jam_buka' => null, 'jam_tutup' => null, 'is_libur' => true],
        ];

        DB::table('master_jam_operasional')->insert($jamOperasional);

        $this->command->info('Seeder Jam Operasional berhasil dijalankan.');

        // 2. Default SLA Target for sub_users
        // Mengisi default SLA menjadi 2 jam untuk user yang belum diset
        $affectedUsers = DB::table('sub_users')
            ->whereNull('sla_target_value')
            ->update([
                'sla_target_value' => 6,
                'sla_target_unit' => 'jam',
            ]);
            
        $this->command->info("Berhasil mengatur default SLA Target untuk {$affectedUsers} sub_users.");
    }
}
