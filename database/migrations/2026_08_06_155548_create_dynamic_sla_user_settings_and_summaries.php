<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom SLA status kustom ke sub_users
        Schema::table('sub_users', function (Blueprint $table) {
            $table->string('sla_start_status')->nullable()->after('sla_target_unit')->comment('Status mulai SLA kustom');
            $table->string('sla_end_status')->nullable()->after('sla_start_status')->comment('Status akhir SLA kustom');
        });

        // 2. Tambah kolom target SLA spesifik per ajuan ke ajuan_sla_summaries
        Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
            $table->integer('target_sla_menit')->default(360)->after('target_sla_menit_aktual')->comment('Target SLA menit spesifik per ajuan');
        });

        // 3. Buat tabel user_ajuan_sla_summaries
        Schema::create('user_ajuan_sla_summaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->unsignedBigInteger('ajuan_id');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('durasi_sla_menit')->nullable();
            $table->dateTime('target_waktu_selesai')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->timestamps();

            // Indexes & Foreign Keys
            $table->foreign('user_id')->references('id')->on('sub_users')->onDelete('cascade');
            $table->index('ajuan_id');
            $table->index(['user_id', 'ajuan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ajuan_sla_summaries');

        Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
            $table->dropColumn('target_sla_menit');
        });

        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropColumn(['sla_start_status', 'sla_end_status']);
        });
    }
};
