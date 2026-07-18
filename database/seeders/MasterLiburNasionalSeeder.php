<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterLiburNasionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // 2025
            ['tanggal' => '2025-01-01', 'keterangan' => 'Tahun Baru 2025 Masehi'],
            ['tanggal' => '2025-01-27', 'keterangan' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['tanggal' => '2025-01-28', 'keterangan' => 'Cuti Bersama Tahun Baru Imlek 2576 Kongzili'],
            ['tanggal' => '2025-01-29', 'keterangan' => 'Tahun Baru Imlek 2576 Kongzili'],
            ['tanggal' => '2025-03-28', 'keterangan' => 'Cuti Bersama Hari Suci Nyepi (Tahun Baru Saka 1947)'],
            ['tanggal' => '2025-03-29', 'keterangan' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)'],
            ['tanggal' => '2025-03-31', 'keterangan' => 'Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-01', 'keterangan' => 'Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-02', 'keterangan' => 'Cuti Bersama Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-03', 'keterangan' => 'Cuti Bersama Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-04', 'keterangan' => 'Cuti Bersama Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-07', 'keterangan' => 'Cuti Bersama Idulfitri 1446 Hijriah'],
            ['tanggal' => '2025-04-18', 'keterangan' => 'Wafat Yesus Kristus'],
            ['tanggal' => '2025-04-20', 'keterangan' => 'Kebangkitan Yesus Kristus (Paskah)'],
            ['tanggal' => '2025-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2025-05-12', 'keterangan' => 'Hari Raya Waisak 2569 BE'],
            ['tanggal' => '2025-05-13', 'keterangan' => 'Cuti Bersama Hari Raya Waisak 2569 BE'],
            ['tanggal' => '2025-05-29', 'keterangan' => 'Kenaikan Yesus Kristus'],
            ['tanggal' => '2025-05-30', 'keterangan' => 'Cuti Bersama Kenaikan Yesus Kristus'],
            ['tanggal' => '2025-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2025-06-06', 'keterangan' => 'Iduladha 1446 Hijriah'],
            ['tanggal' => '2025-06-09', 'keterangan' => 'Cuti Bersama Iduladha 1446 Hijriah'],
            ['tanggal' => '2025-06-27', 'keterangan' => '1 Muharam Tahun Baru Islam 1447 Hijriah'],
            ['tanggal' => '2025-08-17', 'keterangan' => 'Proklamasi Kemerdekaan'],
            ['tanggal' => '2025-09-05', 'keterangan' => 'Maulid Nabi Muhammad SAW'],
            ['tanggal' => '2025-12-25', 'keterangan' => 'Kelahiran Yesus Kristus'],
            ['tanggal' => '2025-12-26', 'keterangan' => 'Cuti Bersama Kelahiran Yesus Kristus'],
            // 2026
            ['tanggal' => '2026-01-01', 'keterangan' => 'Tahun Baru 2026 Masehi'],
            ['tanggal' => '2026-01-16', 'keterangan' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['tanggal' => '2026-02-16', 'keterangan' => 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili'],
            ['tanggal' => '2026-02-17', 'keterangan' => 'Tahun Baru Imlek 2577 Kongzili'],
            ['tanggal' => '2026-03-18', 'keterangan' => 'Cuti Bersama Hari Suci Nyepi (Tahun Baru Saka 1948)'],
            ['tanggal' => '2026-03-19', 'keterangan' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)'],
            ['tanggal' => '2026-03-20', 'keterangan' => 'Cuti Bersama Idulfitri 1447 Hijriah'],
            ['tanggal' => '2026-03-21', 'keterangan' => 'Idulfitri 1447 Hijriah'],
            ['tanggal' => '2026-03-22', 'keterangan' => 'Idulfitri 1447 Hijriah'],
            ['tanggal' => '2026-03-23', 'keterangan' => 'Cuti Bersama Idulfitri 1447 Hijriah'],
            ['tanggal' => '2026-03-24', 'keterangan' => 'Cuti Bersama Idulfitri 1447 Hijriah'],
            ['tanggal' => '2026-04-03', 'keterangan' => 'Wafat Yesus Kristus'],
            ['tanggal' => '2026-04-05', 'keterangan' => 'Kebangkitan Yesus Kristus (Paskah)'],
            ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2026-05-14', 'keterangan' => 'Kenaikan Yesus Kristus'],
            ['tanggal' => '2026-05-15', 'keterangan' => 'Cuti Bersama Kenaikan Yesus Kristus'],
            ['tanggal' => '2026-05-27', 'keterangan' => 'Iduladha 1447 Hijriah'],
            ['tanggal' => '2026-05-28', 'keterangan' => 'Cuti Bersama Iduladha 1447 Hijriah'],
            ['tanggal' => '2026-05-31', 'keterangan' => 'Hari Raya Waisak 2570 BE'],
            ['tanggal' => '2026-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2026-06-16', 'keterangan' => '1 Muharam Tahun Baru Islam 1448 Hijriah'],
            ['tanggal' => '2026-08-17', 'keterangan' => 'Proklamasi Kemerdekaan'],
            ['tanggal' => '2026-08-25', 'keterangan' => 'Maulid Nabi Muhammad SAW'],
            ['tanggal' => '2026-12-24', 'keterangan' => 'Cuti Bersama Kelahiran Yesus Kristus'],
            ['tanggal' => '2026-12-25', 'keterangan' => 'Kelahiran Yesus Kristus'],
        ];

        $now = Carbon::now();

        foreach ($holidays as &$holiday) {
            $holiday['created_at'] = $now;
            $holiday['updated_at'] = $now;
        }

        DB::table('master_libur_nasionals')->insertOrIgnore($holidays);
    }
}
