<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: waktu_selesai harus nullable karena ajuan yang belum selesai
     * belum memiliki waktu_selesai (ajuan_update_datetime = null).
     */
    public function up(): void
    {
        Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
            $table->dateTime('waktu_selesai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ajuan_sla_summaries', function (Blueprint $table) {
            $table->dateTime('waktu_selesai')->nullable(false)->change();
        });
    }
};
