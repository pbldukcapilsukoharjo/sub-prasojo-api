<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Holiday\DestroyBulkHolidayRequest;
use App\Http\Requests\Holiday\ImportHolidayRequest;
use App\Http\Requests\Holiday\IndexHolidayRequest;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Http\Requests\Holiday\UpdateHolidayRequest;
use App\Http\Responses\ApiResponse;
use App\Services\HolidayService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $service
    ) {}

    /**
     * Menampilkan daftar hari libur nasional dengan filter dan paginasi.
     */
    public function index(IndexHolidayRequest $request): JsonResponse
    {
        try {
            $data = $this->service->index($request->validated());
            return ApiResponse::paginated('Berhasil mengambil data hari libur', $data);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Menambahkan data hari libur (single atau bulk).
     */
    public function store(StoreHolidayRequest $request): JsonResponse
    {
        try {
            $data = $this->service->store($request->validated());
            return ApiResponse::success('Berhasil menambahkan data hari libur', $data, 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@store] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal menambahkan data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Unduh template file Excel untuk import data hari libur.
     */
    public function template(): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->generateTemplate();
        } catch (\Throwable $e) {
            Log::error('[HolidayController@template] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengunduh template hari libur', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Mengimpor data hari libur dari file Excel.
     */
    public function import(ImportHolidayRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $data = $this->service->importFromExcel($file);
            return ApiResponse::success('Berhasil mengimpor data hari libur', $data, 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@import] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengimpor data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Menghapus beberapa data hari libur secara massal (bulk delete).
     */
    public function destroyBulk(DestroyBulkHolidayRequest $request): JsonResponse
    {
        try {
            $count = $this->service->destroyBulk($request->validated()['ids']);
            return ApiResponse::success("Berhasil menghapus {$count} data hari libur", ['count' => $count]);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@destroyBulk] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal menghapus data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }


    /**
     * Memperbarui data satu hari libur.
     */
    public function update(UpdateHolidayRequest $request, int $id): JsonResponse
    {
        try {
            $data = $this->service->update($id, $request->validated());
            return ApiResponse::success('Berhasil memperbarui data hari libur', $data);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Hari libur tidak ditemukan', 404);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@update] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal memperbarui data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Menghapus satu data hari libur.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->destroy($id);
            return ApiResponse::success('Berhasil menghapus data hari libur', null);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Hari libur tidak ditemukan', 404);
        } catch (\Throwable $e) {
            Log::error('[HolidayController@destroy] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal menghapus data hari libur', 500, ['error' => $e->getMessage()]);
        }
    }
}
