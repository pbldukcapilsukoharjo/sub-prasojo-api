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
        if (app()->environment('testing') || config('database.default') === 'sqlite' || config('app.env') === 'testing') {
            return;
        }

        try {
            Schema::connection('mysql_prasojo')->table('ajuan', function (Blueprint $table) {
                $table->index('ajuan_status', 'idx_ajuan_status');
                $table->index('ajuan_create_datetime', 'idx_ajuan_create_dt');
                $table->index('ajuan_update_datetime', 'idx_ajuan_update_dt');
                $table->index('ajuan_no_reg', 'idx_ajuan_no_reg');
                $table->index('ajuan_kecamatan_code', 'idx_ajuan_kec_code');
                $table->index('ajuan_is_online', 'idx_ajuan_is_online');
                $table->index('ajuan_pelapor_role_name', 'idx_ajuan_pelapor_role');
                $table->index('ajuan_pelapor_id', 'idx_ajuan_pelapor_id');
            });
        } catch (\Exception $e) {
            // Ignore if indexes already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('testing') || config('database.default') === 'sqlite' || config('app.env') === 'testing') {
            return;
        }

        Schema::connection('mysql_prasojo')->table('ajuan', function (Blueprint $table) {
            $table->dropIndex('idx_ajuan_status');
            $table->dropIndex('idx_ajuan_create_dt');
            $table->dropIndex('idx_ajuan_update_dt');
            $table->dropIndex('idx_ajuan_no_reg');
            $table->dropIndex('idx_ajuan_kec_code');
            $table->dropIndex('idx_ajuan_is_online');
            $table->dropIndex('idx_ajuan_pelapor_role');
            $table->dropIndex('idx_ajuan_pelapor_id');
        });
    }
};
