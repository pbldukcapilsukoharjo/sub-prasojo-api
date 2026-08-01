<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MasterLiburNasional;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class HolidayService
{
    /**
     * Get paginated list of holidays with optional year and search filters.
     */
    public function index(array $filters): LengthAwarePaginator
    {
        try {
            $query = MasterLiburNasional::query();

            if (!empty($filters['tahun'])) {
                $query->whereYear('tanggal', (int) $filters['tahun']);
            }

            if (!empty($filters['search'])) {
                $query->where('keterangan', 'like', '%' . $filters['search'] . '%');
            }

            $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;

            return $query->orderBy('tanggal', 'asc')->paginate($perPage);
        } catch (\Throwable $e) {
            Log::error('[HolidayService@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Store bulk or single holiday entries within a database transaction.
     */
    public function store(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $holidays = $data['holidays'] ?? [];

                $dates = array_column($holidays, 'tanggal');

                // Check duplicate dates inside input array
                if (count($dates) !== count(array_unique($dates))) {
                    throw new InvalidArgumentException('Terdapat tanggal duplikat dalam data yang dikirim.');
                }

                // Check existing dates in database
                $existing = MasterLiburNasional::whereIn('tanggal', $dates)
                    ->pluck('tanggal')
                    ->map(fn ($d) => is_string($d) ? $d : $d->format('Y-m-d'))
                    ->toArray();

                if (count($existing) > 0) {
                    throw new InvalidArgumentException(
                        'Tanggal berikut sudah terdaftar sebagai hari libur: ' . implode(', ', $existing)
                    );
                }

                $now = now();
                $insertData = array_map(function ($item) use ($now) {
                    return [
                        'tanggal' => $item['tanggal'],
                        'keterangan' => $item['keterangan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $holidays);

                MasterLiburNasional::insert($insertData);

                return MasterLiburNasional::whereIn('tanggal', $dates)
                    ->orderBy('tanggal', 'asc')
                    ->get()
                    ->toArray();
            });
        } catch (\Throwable $e) {
            Log::error('[HolidayService@store] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Show single holiday entry by ID.
     */
    public function show(int $id): MasterLiburNasional
    {
        try {
            return MasterLiburNasional::findOrFail($id);
        } catch (\Throwable $e) {
            Log::error('[HolidayService@show] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a single holiday entry.
     */
    public function update(int $id, array $data): MasterLiburNasional
    {
        try {
            $holiday = MasterLiburNasional::findOrFail($id);

            $currentTanggal = is_string($holiday->tanggal)
                ? $holiday->tanggal
                : $holiday->tanggal->format('Y-m-d');

            if (isset($data['tanggal']) && $data['tanggal'] !== $currentTanggal) {
                $exists = MasterLiburNasional::where('tanggal', $data['tanggal'])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    throw new InvalidArgumentException(
                        "Tanggal {$data['tanggal']} sudah terdaftar sebagai hari libur."
                    );
                }
            }

            $holiday->update($data);
            return $holiday->fresh();
        } catch (\Throwable $e) {
            Log::error('[HolidayService@update] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a single holiday entry by ID.
     */
    public function destroy(int $id): bool
    {
        try {
            $holiday = MasterLiburNasional::findOrFail($id);
            return (bool) $holiday->delete();
        } catch (\Throwable $e) {
            Log::error('[HolidayService@destroy] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete multiple holiday entries by IDs.
     */
    public function destroyBulk(array $ids): int
    {
        try {
            return DB::transaction(function () use ($ids) {
                return MasterLiburNasional::whereIn('id', $ids)->delete();
            });
        } catch (\Throwable $e) {
            Log::error('[HolidayService@destroyBulk] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
