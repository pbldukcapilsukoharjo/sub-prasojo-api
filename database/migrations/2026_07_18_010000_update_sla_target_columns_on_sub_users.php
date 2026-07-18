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
        Schema::table('sub_users', function (Blueprint $table) {
            $table->integer('sla_target_value')->nullable()->after('hashed_password')->comment('Nilai target SLA');
            $table->enum('sla_target_unit', ['menit', 'jam', 'hari'])->nullable()->after('sla_target_value')->comment('Satuan target SLA');
        });

        DB::table('sub_users')->whereNotNull('sla_target_hours')->update([
            'sla_target_value' => DB::raw('sla_target_hours'),
            'sla_target_unit' => 'jam',
        ]);

        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropColumn('sla_target_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->integer('sla_target_hours')->nullable()->after('hashed_password')->comment('Target SLA dinamis per akun (dalam jam)');
        });

        DB::table('sub_users')->where('sla_target_unit', 'jam')->update([
            'sla_target_hours' => DB::raw('sla_target_value'),
        ]);

        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropColumn(['sla_target_value', 'sla_target_unit']);
        });
    }
};
