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
        $validated = $request->validate([
            'jam_buka' => 'nullable|date_format:H:i:s',
            'jam_tutup' => 'nullable|date_format:H:i:s',
            'is_libur' => 'required|boolean',
        ]);

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
