<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\SlaFilter;
use App\Models\Ajuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

final class SlaService
{
    public function getKpi(SlaFilter $filter): array
    {
        $requestParams = request()->all();
        $cacheKey = 'sla:kpi:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filter) {
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $statusSelesai = AjuanStatus::getStatusSelesai();

            $defaultSla = config('sla.default_jam', 6) * 60;
            $perLayanan = config('sla.per_layanan', []);

            $caseSql = "CASE ";
            foreach ($perLayanan as $kode => $jam) {
                $menit = $jam * 60;
                $caseSql .= "WHEN ajuan_layanan_kode = '{$kode}' THEN {$menit} ";
            }
            $caseSql .= "ELSE {$defaultSla} END";

            $sqlMemenuhi = "SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) <= ($caseSql) THEN 1 ELSE 0 END) as total_memenuhi";

            $kpiData = (clone $query)->whereIn('ajuan_status', $statusSelesai)
                ->select(
                    DB::raw('COUNT(ajuan_id) as total_selesai'),
                    DB::raw($sqlMemenuhi),
                    DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime)) as rata_rata_menit')
                )->first();

            $totalSelesai = (int)($kpiData->total_selesai ?? 0);
            $totalMemenuhi = (int)($kpiData->total_memenuhi ?? 0);
            $rataRataMenit = (float)($kpiData->rata_rata_menit ?? 0);

            $capaianPersen = $totalSelesai > 0 ? round(($totalMemenuhi / $totalSelesai) * 100, 2) : 0.0;

            $jam = floor($rataRataMenit / 60);
            $menit = round($rataRataMenit % 60);
            $slaText = "";
            if ($jam > 0) {
                $slaText .= $jam . " Jam ";
            }
            $slaText .= $menit . " Menit";
            $slaText = trim($slaText);
            if ($slaText === "0 Menit") {
                $slaText = "0 Menit";
            }

            return [
                'rata_rata_global_text' => $slaText,
                'capaian_sla_persen' => $capaianPersen,
            ];
        });
    }

    public function getLayanan(SlaFilter $filter)
    {
        $query = Ajuan::query()->from('ajuan');
        $query = $filter->apply($query);

        $statusSelesai = AjuanStatus::getStatusSelesai();
        $perPage = $filter->request['per_page'] ?? 10;

        $data = $query->join('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
            ->whereIn('ajuan.ajuan_status', $statusSelesai)
            ->select(
                'layanan.layanan_kode',
                'layanan.layanan_nama',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime)) as rata_rata_menit')
            )
            ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
            ->paginate($perPage);

        $defaultSla = config('sla.default_jam', 6) * 60;
        $perLayanan = config('sla.per_layanan', []);

        $data->getCollection()->transform(function ($item) use ($defaultSla, $perLayanan) {
            $rataRataMenit = (float)$item->rata_rata_menit;
            $jamAktual = floor($rataRataMenit / 60);
            $menitAktual = round($rataRataMenit % 60);
            $aktualText = "";
            if ($jamAktual > 0) $aktualText .= $jamAktual . " Jam ";
            $aktualText .= $menitAktual . " Menit";
            
            $targetMenit = isset($perLayanan[$item->layanan_kode]) 
                ? $perLayanan[$item->layanan_kode] * 60 
                : $defaultSla;

            $statusSla = $rataRataMenit <= $targetMenit ? 'MEMENUHI' : 'TIDAK MEMENUHI';
            
            $targetJam = floor($targetMenit / 60);
            $targetMenitSisa = $targetMenit % 60;
            $targetText = "";
            if ($targetJam > 0) $targetText .= $targetJam . " Jam ";
            if ($targetMenitSisa > 0) $targetText .= $targetMenitSisa . " Menit";

            return [
                'layanan_kode' => $item->layanan_kode,
                'nama_layanan' => $item->layanan_nama,
                'target_sla' => trim($targetText),
                'aktual_rata_rata' => trim($aktualText),
                'status_sla' => $statusSla,
            ];
        });

        return $data;
    }

    public function exportLayanan(SlaFilter $filter)
    {
        $query = Ajuan::query()->from('ajuan');
        $query = $filter->apply($query);

        $statusSelesai = AjuanStatus::getStatusSelesai();

        $data = $query->join('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
            ->whereIn('ajuan.ajuan_status', $statusSelesai)
            ->select(
                'layanan.layanan_kode',
                'layanan.layanan_nama',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime)) as rata_rata_menit')
            )
            ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
            ->get();

        $defaultSla = config('sla.default_jam', 6) * 60;
        $perLayanan = config('sla.per_layanan', []);

        return $data->map(function ($item) use ($defaultSla, $perLayanan) {
            $rataRataMenit = (float)$item->rata_rata_menit;
            $jamAktual = floor($rataRataMenit / 60);
            $menitAktual = round($rataRataMenit % 60);
            $aktualText = "";
            if ($jamAktual > 0) $aktualText .= $jamAktual . " Jam ";
            $aktualText .= $menitAktual . " Menit";
            
            $targetMenit = isset($perLayanan[$item->layanan_kode]) 
                ? $perLayanan[$item->layanan_kode] * 60 
                : $defaultSla;

            $statusSla = $rataRataMenit <= $targetMenit ? 'MEMENUHI' : 'TIDAK MEMENUHI';
            
            $targetJam = floor($targetMenit / 60);
            $targetMenitSisa = $targetMenit % 60;
            $targetText = "";
            if ($targetJam > 0) $targetText .= $targetJam . " Jam ";
            if ($targetMenitSisa > 0) $targetText .= $targetMenitSisa . " Menit";

            return [
                'layanan_kode' => $item->layanan_kode,
                'nama_layanan' => $item->layanan_nama,
                'target_sla' => trim($targetText),
                'aktual_rata_rata' => trim($aktualText),
                'status_sla' => $statusSla,
            ];
        });
    }
}