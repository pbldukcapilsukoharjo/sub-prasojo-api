<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom SLA target ke sub_users
        if (Schema::hasTable('sub_users') && !Schema::hasColumn('sub_users', 'sla_target_hours')) {
            Schema::table('sub_users', function (Blueprint $table) {
                $table->integer('sla_target_hours')->nullable()->after('hashed_password')->comment('Target SLA dinamis per akun (dalam jam)');
            });
        }

        // 2. Modifikasi ajuan_sla_summaries
        if (Schema::hasTable('ajuan_sla_summaries')) {
            Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
                $table->unsignedBigInteger('operator_user_id')->nullable()->after('ajuan_id')->comment('User ID operator dari log_ajuan_status');
                $table->integer('target_sla_menit_aktual')->nullable()->after('operator_user_id')->comment('Snapshot target SLA operator saat ajuan selesai');
                $table->integer('durasi_kondisi_a_menit')->nullable()->after('durasi_sla_menit')->comment('Durasi SLA End-to-End');
                $table->integer('durasi_kondisi_b_menit')->nullable()->after('durasi_kondisi_a_menit')->comment('Durasi SLA Verifikasi Terbaru');
                $table->dateTime('target_waktu_selesai_kondisi_a')->nullable()->after('durasi_kondisi_b_menit')->comment('Pre-calculated deadline SLA End-to-End');
                $table->dateTime('target_waktu_selesai_kondisi_b')->nullable()->after('target_waktu_selesai_kondisi_a')->comment('Pre-calculated deadline SLA Verifikasi Terbaru');
            });
        }

        // 3. Buat tabel master_jam_operasional
        if (!Schema::hasTable('master_jam_operasional')) {
            Schema::create('master_jam_operasional', function (Blueprint $table) {
                $table->id();
                $table->integer('hari_kode')->unique()->comment('1 = Senin, 7 = Minggu');
                $table->string('hari_nama', 20);
                $table->time('jam_buka')->nullable();
                $table->time('jam_tutup')->nullable();
                $table->boolean('is_libur')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jam_operasional');

        if (Schema::hasTable('ajuan_sla_summaries')) {
            Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
                $table->dropColumn([
                    'operator_user_id', 
                    'target_sla_menit_aktual', 
                    'durasi_kondisi_a_menit', 
                    'durasi_kondisi_b_menit',
                    'target_waktu_selesai_kondisi_a',
                    'target_waktu_selesai_kondisi_b'
                ]);
            });
        }

        if (Schema::hasTable('sub_users') && Schema::hasColumn('sub_users', 'sla_target_hours')) {
            Schema::table('sub_users', function (Blueprint $table) {
                $table->dropColumn('sla_target_hours');
            });
        }
    }
};
