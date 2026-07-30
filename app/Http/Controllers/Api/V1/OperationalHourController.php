<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperationalHourController extends Controller
{
    /**
     * List Master Jam Operasional
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $hours = \App\Models\MasterJamOperasional::orderBy('hari_kode')->get();

            if ($hours->isEmpty()) {
                $defaultHours = [
                    ['hari_kode' => 1, 'hari_nama' => 'Senin', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
                    ['hari_kode' => 2, 'hari_nama' => 'Selasa', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
                    ['hari_kode' => 3, 'hari_nama' => 'Rabu', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
                    ['hari_kode' => 4, 'hari_nama' => 'Kamis', 'jam_buka' => '08:00:00', 'jam_tutup' => '15:00:00', 'is_libur' => false],
                    ['hari_kode' => 5, 'hari_nama' => 'Jumat', 'jam_buka' => '08:00:00', 'jam_tutup' => '13:00:00', 'is_libur' => false],
                    ['hari_kode' => 6, 'hari_nama' => 'Sabtu', 'jam_buka' => null, 'jam_tutup' => null, 'is_libur' => true],
                    ['hari_kode' => 7, 'hari_nama' => 'Minggu', 'jam_buka' => null, 'jam_tutup' => null, 'is_libur' => true],
                ];

                foreach ($defaultHours as $item) {
                    \App\Models\MasterJamOperasional::create($item);
                }

                $hours = \App\Models\MasterJamOperasional::orderBy('hari_kode')->get();
            }

            return response()->json([
                'status' => 'success',
                'data' => $hours,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[OperationalHourController@index] ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mendapatkan data jam operasional', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update Jam Operasional (Harian)
     */
    public function update(\Illuminate\Http\Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        // 1. Normalisasi string booleans ("true"/"false")
        if ($request->has('is_libur')) {
            $isLibur = $request->input('is_libur');
            if ($isLibur === 'true' || $isLibur === 1 || $isLibur === '1') {
                $request->merge(['is_libur' => true]);
            } elseif ($isLibur === 'false' || $isLibur === 0 || $isLibur === '0') {
                $request->merge(['is_libur' => false]);
            }
        }

        // 2. Normalisasi format jam HH:MM -> HH:MM:00
        if ($request->filled('jam_buka') && preg_match('/^\d{2}:\d{2}$/', $request->jam_buka)) {
            $request->merge(['jam_buka' => $request->jam_buka . ':00']);
        }
        if ($request->filled('jam_tutup') && preg_match('/^\d{2}:\d{2}$/', $request->jam_tutup)) {
            $request->merge(['jam_tutup' => $request->jam_tutup . ':00']);
        }

        // 3. Validasi dengan required_if untuk jam buka/tutup ketika is_libur = false
        $validated = $request->validate([
            'jam_buka' => 'required_if:is_libur,false,0|nullable|date_format:H:i:s',
            'jam_tutup' => 'required_if:is_libur,false,0|nullable|date_format:H:i:s',
            'is_libur' => 'required|boolean',
        ]);

        // 4. Jika is_libur adalah true, set jam buka dan jam tutup ke null
        if ($validated['is_libur']) {
            $validated['jam_buka'] = null;
            $validated['jam_tutup'] = null;
        }

        try {
            $hour = \App\Models\MasterJamOperasional::findOrFail($id);
            $hour->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Jam operasional berhasil diperbarui.',
                'data' => $hour,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Data jam operasional tidak ditemukan'], 404);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[OperationalHourController@update] ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui jam operasional', 'error' => $e->getMessage()], 500);
        }
    }
}
