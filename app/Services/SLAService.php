<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ajuan;
use App\Models\LogAjuanStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SlaService
{
    private const TARGET_SLA = 6;

    /**
     * Get list of SLA data with pagination
     * 
     * @param array<string,mixed> $filter
     * @return array<string,mixed>
     */
    public function getList(array $filter): array
    {
        $page = (int) ($filter['page'] ?? 1);
        $perPage = 5;

        $query = Ajuan::query()
            ->with('layanan')
            ->whereIn('ajuan_status', [
                Ajuan::STATUS_SELESAI,
                Ajuan::STATUS_DITOLAK,
                Ajuan::STATUS_DIBATALKAN,
            ]);

        $paginator = $query->paginate(
            perPage: $perPage,
            page: $page,
        );

        $rows = collect($paginator->items());

        $list = $rows
            ->groupBy('ajuan_layanan_kode')
            ->values()
            ->map(function (Collection $items, int $index): array {
                $avg = round(
                    $items->avg(function (Ajuan $ajuan) {
                        return $ajuan->ajuan_update_datetime
                            ? $ajuan->ajuan_create_datetime
                                ->diffInMinutes($ajuan->ajuan_update_datetime) / 60
                            : 0;
                    }),
                    1
                );

                return [
                    'id' => $index + 1,
                    'jenis_layanan' => optional($items->first()?->layanan)->layanan_nama,
                    'jumlah_ajuan' => $items->count(),
                    'rata_rata_waktu' => $avg,
                ];
            });

        $rataRata = round($list->avg('rata_rata_waktu'), 1);
        $capaian = $rataRata <= self::TARGET_SLA
            ? 100
            : max(0, round((self::TARGET_SLA / $rataRata) * 100, 1));

        return [
            'rata_rata_waktu_proses' => $rataRata,
            'pencapaian_sla' => $capaian,
            'target_sla' => self::TARGET_SLA,
            'daftar_rincian' => [
                'list' => $list->values(),
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'total_page' => $paginator->lastPage(),
                ],
            ],
        ];
    }

    /**
     * Get KPI data for SLA
     * 
     * @param array<string,mixed> $filter
     * @return array<string,mixed>
     */
    public function getKpi(array $filter): array
    {
        $items = $this->buildBaseQuery($filter)->get();

        $avg = $items->avg('sla_duration') ?? 0;

        $pass = $items
            ->where('sla_duration', '<=', self::TARGET_SLA)
            ->count();

        return [
            'rata_rata_global_text' => $this->formatHours($avg),
            'capaian_sla_persen' =>
                $items->count()
                ? round(($pass / $items->count()) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get SLA data per layanan with pagination
     * 
     * @param array<string,mixed> $filter
     * @return LengthAwarePaginator
     */
    public function getLayanan(array $filter): LengthAwarePaginator
    {
        $perPage = $filter['per_page'] ?? 10;

        $paginated = $this->buildBaseQuery($filter)->paginate($perPage);

        $transformedItems = $paginated->getCollection()->map(
            fn ($ajuan) => $this->mapSla($ajuan)
        );

        $paginated->setCollection($transformedItems);

        return $paginated;
    }

    /**
     * Build base query for SLA calculations
     * 
     * @param array<string,mixed> $filter
     * @return Builder
     */
    private function buildBaseQuery(array $filter): Builder
    {
        return Ajuan::query()
            ->with([
                'layanan',
                'logStatuses.admin',
            ])
            ->whereIn('ajuan_status', [
                Ajuan::STATUS_SELESAI,
                Ajuan::STATUS_DITOLAK,
                Ajuan::STATUS_DIBATALKAN,
            ])
            ->when(
                $filter['layanan_kode'] ?? null,
                fn ($q, $v) => $q->byLayanan($v)
            );
    }

    /**
     * Map SLA data for a single Ajuan
     * 
     * @param Ajuan $ajuan
     * @return array<string,mixed>
     */
    private function mapSla(Ajuan $ajuan): array
    {
        $start =
            $ajuan->logStatuses
                ->where('log_status', Ajuan::STATUS_DIPROSES)
                ->sortBy('log_create_datetime')
                ->first()
                ?->log_create_datetime
            ?? $ajuan->ajuan_create_datetime;

        $finish =
            $ajuan->logStatuses
                ->whereIn('log_status', [
                    Ajuan::STATUS_SELESAI,
                    Ajuan::STATUS_DITOLAK,
                    Ajuan::STATUS_DIBATALKAN,
                ])
                ->sortByDesc('log_create_datetime')
                ->first()
                ?->log_create_datetime;

        $hours = round(
            Carbon::parse($start)
                ->diffInMinutes($finish) / 60,
            2
        );

        return [
            'ajuan_id' => $ajuan->ajuan_id,
            'jenis_layanan' => $ajuan->layanan?->layanan_nama,
            'jumlah_ajuan' => 1,
            'rata_rata_waktu' => $hours,
            'sla_duration' => $hours,
            'target_sla' => self::TARGET_SLA,
            'status_sla' =>
                $hours <= self::TARGET_SLA
                    ? 'TEPAT_WAKTU'
                    : 'TERLAMBAT',
        ];
    }

    /**
     * Format hours into readable format
     * 
     * @param float $hours
     * @return string
     */
    private function formatHours(float $hours): string
    {
        $minute = (int) round($hours * 60);

        return floor($minute / 60)
            . ' Jam '
            . ($minute % 60)
            . ' Menit';
    }
}