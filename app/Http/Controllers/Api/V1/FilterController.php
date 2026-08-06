<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Prasojo\Ajuan;
use App\Models\Prasojo\IlokasiKecamatan;
use App\Models\Prasojo\Layanan;
use App\Models\Prasojo\JenisAjuan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class FilterController extends Controller
{
    /**
     * Filter Layanan
     */
    public function layanan(Request $request): JsonResponse
    {
        $query = Layanan::select('layanan_kode as id', 'layanan_nama as name')
            ->where('layanan_is_active', 1);

        if ($request->filled('pelapor')) {
            $pelapor = (string) $request->input('pelapor');
            $pelaporLower = strtolower($pelapor);

            $ajuanQuery = Ajuan::query()->distinct();

            if ($pelaporLower === 'online') {
                $ajuanQuery->where('ajuan_is_online', 1);
            } elseif ($pelaporLower === 'offline') {
                $ajuanQuery->where('ajuan_is_online', 0);
            } elseif ($pelaporLower === 'mandiri') {
                $ajuanQuery->where('ajuan_is_mandiri', 1);
            } elseif ($pelaporLower === 'operator') {
                $ajuanQuery->where('ajuan_is_mandiri', 0);
            } elseif ($pelaporLower === 'tamat') {
                $ajuanQuery->whereRaw('UPPER(TRIM(ajuan_keterangan)) = ?', ['TAMAT']);
            } else {
                $ajuanQuery->where('ajuan_pelapor_role_name', 'LIKE', '%' . $pelapor . '%');
            }

            $layananKodes = $ajuanQuery->pluck('ajuan_layanan_kode')->toArray();

            $query->whereIn('layanan_kode', $layananKodes);
        }

        $data = $query->orderBy('layanan_pos')->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter layanan',
            'data' => $data,
        ]);
    }

    /**
     * Filter Kecamatan
     */
    public function kecamatan(): JsonResponse
    {
        // Berdasarkan IlokasiKecamatan
        $data = IlokasiKecamatan::select('kecamatan_code as id', 'kecamatan_name as name')
            ->where('kecamatan_kabupaten_id', '3311')
            ->orderBy('kecamatan_name')
            ->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter kecamatan',
            'data' => $data,
        ]);
    }

    /**
     * Filter Pelapor
     */
    public function pelapor(): JsonResponse
    {
        $dbRoles = Ajuan::query()
            ->whereNotNull('ajuan_pelapor_role_name')
            ->where('ajuan_pelapor_role_name', '!=', '')
            ->distinct()
            ->pluck('ajuan_pelapor_role_name')
            ->toArray();

        if (!in_array('TAMAT', $dbRoles)) {
            $dbRoles[] = 'TAMAT';
        }

        sort($dbRoles);

        $data = array_map(function ($role) {
            return [
                'id' => $role,
                'name' => $role,
            ];
        }, $dbRoles);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter pelapor',
            'data' => $data,
        ]);
    }

    /**
     * Filter Status Ajuan
     */
    public function status(): JsonResponse
    {
        // 1. Ambil status dinamis / legacy yang ada di database
        $dbStatuses = Ajuan::query()
            ->whereNotNull('ajuan_status')
            ->where('ajuan_status', '!=', '')
            ->distinct()
            ->pluck('ajuan_status')
            ->toArray();

        // 2. Gabungkan dengan hardcode Model, lalu hilangkan duplikat (array_unique)
        $allStatuses = array_unique(array_merge(Ajuan::STATUSES, $dbStatuses));
        
        // Opsional: Urutkan secara alfabet agar rapi di dropdown
        sort($allStatuses);

        $data = array_map(function ($status) {
            return [
                'id' => $status,
                'name' => $status,
            ];
        }, $allStatuses);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter status',
            'data' => array_values($data),
        ]);
    }

    /**
     * Filter Jenis Ajuan
     */
    public function jenisAjuan(): JsonResponse
    {
        $data = JenisAjuan::select('ja_id as id', 'ja_judul as name')
            ->orderBy('ja_judul')
            ->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter jenis ajuan',
            'data' => $data,
        ]);
    }

    /**
     * Filter Jalur
     */
    public function jalur(): JsonResponse
    {
        $data = [
            ['id' => '1', 'name' => 'Online'],
            ['id' => '0', 'name' => 'Offline'],
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil filter jalur',
            'data' => $data,
        ]);
    }
}
