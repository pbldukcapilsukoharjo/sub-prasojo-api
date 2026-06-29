<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ajuan;
use App\Models\LembarKerja;
use App\Models\Produk;
use App\Filters\AjuanFilter;
use App\Filters\LembarKerjaFilter;
use App\Filters\ProdukFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanService
{
    public function getAjuanList(array $filters): LengthAwarePaginator
    {
        $query = Ajuan::query()->with(['pelapor', 'layanan']);
        
        $filter = new AjuanFilter();
        $query = $filter->apply($query, $filters);
        
        $paginator = $query->paginate((int) request('per_page', 10));
        
        $paginator->getCollection()->transform(function (Ajuan $ajuan) {
            return $this->formatAjuan($ajuan);
        });

        return $paginator;
    }

    public function getLembarKerjaList(array $filters): array
    {
        $query = LembarKerja::query()->with(['ajuan.pelapor', 'ajuan.layanan']);
        
        $filter = new LembarKerjaFilter();
        $query = $filter->apply($query, $filters);
        
        // Calculate chart data before pagination
        // Need to clone query without eager loading for faster aggregation
        $baseChartQuery = (clone $query)->without(['ajuan.pelapor', 'ajuan.layanan']);
        
        // 1. Chart Status (Donut Chart)
        $chartStatus = (clone $baseChartQuery)
            ->select('lk_status as label', DB::raw('count(*) as count'))
            ->groupBy('lk_status')
            ->get()
            ->map(function($item) use ($baseChartQuery) {
                // To get percentage, we need total
                // We'll calculate percentage on the frontend, just return count
                return [
                    'label' => $item->label,
                    'value' => $item->count
                ];
            });

        // 2. Chart Layanan (Bar Chart)
        $chartLayanan = (clone $baseChartQuery)
            ->join('layanan', 'lembar_kerja.lk_layanan_kode', '=', 'layanan.layanan_kode')
            ->select('layanan.layanan_nama as label', DB::raw('count(lembar_kerja.lk_id) as count'))
            ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
            ->get()
            ->map(function($item) {
                return [
                    'label' => $item->label,
                    'value' => $item->count
                ];
            });

        $paginator = $query->paginate((int) request('per_page', 10));
        
        $paginator->getCollection()->transform(function (LembarKerja $lk) {
            $ajuan = $lk->ajuan;
            $layananNama = $ajuan && $ajuan->layanan ? $ajuan->layanan->layanan_nama : $lk->lk_layanan_kode;
            $pelaporName = $ajuan && $ajuan->pelapor ? $ajuan->pelapor->fullname : 'Unknown';
            
            // Format according to what frontend expects
            return [
                'id' => $lk->lk_id,
                'no_reg' => $lk->lk_ajuan_no_reg,
                'layanan' => $layananNama,
                'kecamatan' => $ajuan ? $ajuan->ajuan_kecamatan_name : null,
                'pelapor' => $lk->lk_pelapor_role_name,
                'status' => $lk->lk_status,
                'created_at' => $lk->lk_create_datetime ? $lk->lk_create_datetime->format('Y-m-d H:i:s') : null,
            ];
        });

        return [
            'paginator' => $paginator,
            'chart_status' => $chartStatus,
            'chart_layanan' => $chartLayanan
        ];
    }

    public function getProdukList(array $filters): LengthAwarePaginator
    {
        $query = Produk::query()
            ->with(['ajuan.pelapor', 'ajuan.layanan', 'ajuan' => function($q) {
                $q->with([
                    'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                    'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
                ]);
            }]);
            
        $filter = new ProdukFilter();
        $query = $filter->apply($query, $filters);
        
        $paginator = $query->paginate((int) request('per_page', 10));
        
        $paginator->getCollection()->transform(function (Produk $produk) {
            $ajuan = $produk->ajuan;
            $layananNama = $ajuan && $ajuan->layanan ? $ajuan->layanan->layanan_nama : $produk->prod_layanan_kode;
            $pelaporName = $ajuan && $ajuan->pelapor ? $ajuan->pelapor->fullname : 'Unknown';
            $pelaporType = $ajuan && $ajuan->isOnline() ? 'Online' : 'Offline';

            $data = [
                'id' => $produk->prod_id,
                'no_reg' => $produk->prod_ajuan_no_reg,
                'layanan' => $layananNama,
                'kecamatan' => $ajuan ? $ajuan->ajuan_kecamatan_name : null,
                'pelapor' => sprintf('%s (%s)', $pelaporName, $pelaporType),
                'status' => $produk->prod_status,
                'created_at' => $produk->prod_create_datetime ? $produk->prod_create_datetime->format('Y-m-d H:i:s') : null,
            ];
            
            $namaIdentitas = '-';
            
            if ($ajuan) {
                $detailModel = $ajuan->getDetailRelation()?->first();
                if ($detailModel) {
                    $namaIdentitas = $detailModel->ajakel_nama_bayi 
                                  ?? $detailModel->ajakem_nama_jenazah 
                                  ?? $detailModel->ajd_nama_lengkap 
                                  ?? $detailModel->ajkia_nama_lengkap 
                                  ?? $detailModel->ajkk_nama_kepala_keluarga 
                                  ?? $detailModel->ajktpel_nama_lengkap 
                                  ?? $detailModel->ajp_nama_lengkap 
                                  ?? $detailModel->ajrj_nama_lengkap 
                                  ?? $detailModel->ajud_nama_lengkap 
                                  ?? '-';
                }
            }
            
            if ($namaIdentitas === '-') {
                if ($produk->prod_nama) {
                    $namaIdentitas = $produk->prod_nama;
                }
            }
            
            $data['nama_identitas_produk'] = $namaIdentitas;
            $data['nomor'] = $produk->prod_nomor;
            
            return $data;
        });

        return $paginator;
    }

    public function getDetailTimeline(int $ajuanId): array
    {
        $ajuan = Ajuan::query()->with(['logStatuses' => function($q) {
            $q->orderBy('log_create_datetime', 'asc');
        }])->findOrFail($ajuanId);
        
        $logs = $ajuan->logStatuses->map(function ($log) {
            return [
                'status' => $log->log_status,
                'note' => $log->log_note,
                'datetime' => $log->log_create_datetime ? $log->log_create_datetime->format('Y-m-d H:i:s') : null,
            ];
        });

        return [
            'ajuan_id' => $ajuan->ajuan_id,
            'no_reg' => $ajuan->ajuan_no_reg,
            'status_saat_ini' => $ajuan->ajuan_status,
            'timeline' => $logs->toArray()
        ];
    }

    private function formatAjuan(Ajuan $ajuan): array
    {
        $layananNama = $ajuan->layanan ? $ajuan->layanan->layanan_nama : $ajuan->ajuan_layanan_kode;
        $pelaporName = $ajuan->pelapor ? $ajuan->pelapor->fullname : 'Unknown';
        $pelaporType = $ajuan->isOnline() ? 'Online' : 'Offline';
        
        return [
            'id' => $ajuan->ajuan_id,
            'no_reg' => $ajuan->ajuan_no_reg,
            'layanan' => $layananNama,
            'kecamatan' => $ajuan->ajuan_kecamatan_name,
            'pelapor' => sprintf('%s (%s)', $pelaporName, $pelaporType),
            'status' => $ajuan->ajuan_status,
            'created_at' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('Y-m-d H:i:s') : null,
        ];
    }
}
