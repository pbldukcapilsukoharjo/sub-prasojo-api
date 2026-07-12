<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Prasojo\Ajuan;
use App\Models\Prasojo\LembarKerja;
use App\Models\Prasojo\Produk;
use App\Filters\AjuanFilter;
use App\Filters\LembarKerjaFilter;
use App\Filters\ProdukFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PengajuanService
{
    public function getAjuanList(array $filters): LengthAwarePaginator
    {
        try {
            $query = Ajuan::query()->with([
                'pelapor', 
                'layanan',
                'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
            ]);
            
            $filter = new AjuanFilter();
            $query = $filter->apply($query, $filters);
            
            $paginator = $query->paginate((int) request('per_page', 10));
            
            $paginator->getCollection()->transform(function (Ajuan $ajuan) {
                return $this->formatAjuan($ajuan);
            });

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@getAjuanList] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getAjuanChart(array $filters): array
    {
        try {
            $query = Ajuan::query();
            
            $filter = new AjuanFilter();
            $query = $filter->apply($query, $filters);
            
            $baseChartQuery = $query->without(['pelapor', 'layanan']);
            
            $chartStatus = (clone $baseChartQuery)
                ->reorder()
                ->select('ajuan_status as label', DB::raw('count(*) as count'))
                ->groupBy('ajuan_status')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => $item->label,
                        'value' => $item->count
                    ];
                });

            $chartLayanan = (clone $baseChartQuery)
                ->reorder()
                ->join('layanan', 'ajuan.ajuan_layanan_kode', '=', 'layanan.layanan_kode')
                ->select('layanan.layanan_nama as label', DB::raw('count(ajuan.ajuan_id) as count'))
                ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => $item->label,
                        'value' => $item->count
                    ];
                });
                
            return [
                'chart_status' => $chartStatus,
                'chart_layanan' => $chartLayanan
            ];
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@getAjuanChart] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getLembarKerjaList(array $filters): LengthAwarePaginator
    {
        try {
            $query = LembarKerja::query()->with([
                'ajuan.pelapor', 
                'ajuan.layanan',
                'ajuan' => function($q) {
                    $q->with([
                        'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                        'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
                    ]);
                }
            ]);
            
            $filter = new LembarKerjaFilter();
            $query = $filter->apply($query, $filters);
            $paginator = $query->paginate((int) request('per_page', 10));
            
            $paginator->getCollection()->transform(function (LembarKerja $lk) {
                $ajuan = $lk->ajuan;
                
                $jalur = $lk->lk_ajuan_is_online ? 'Online' : 'Offline';
                $layananNama = $ajuan && $ajuan->layanan ? $ajuan->layanan->layanan_nama : $lk->lk_layanan_kode;

                // Format according to what frontend expects
                return [
                    'id' => $lk->lk_id,
                    'no_reg' => $lk->lk_ajuan_no_reg,
                    'kode_ajuan' => $lk->lk_ajuan_id,
                    'kode_produk' => $lk->lk_produk_id,
                    'layanan' => $layananNama,
                    'jalur' => $jalur,
                    'pelapor' => $lk->lk_pelapor_role_name,
                    'status' => $lk->lk_status,
                    'tanggal' => $lk->lk_create_datetime ? $lk->lk_create_datetime->format('Y-m-d H:i:s') : null,
                    'kecamatan' => $ajuan ? $ajuan->ajuan_kecamatan_name : null,
                    'data_ajuan' => $ajuan ? $ajuan->getDetailData() : null,
                ];
            });

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@getLembarKerjaList] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getProdukList(array $filters): LengthAwarePaginator
    {
        try {
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
                    $detailModel = $ajuan->getDetailData();
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
                $data['data_ajuan'] = $ajuan ? $ajuan->getDetailData() : null;
                
                return $data;
            });

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@getProdukList] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getDetailTimeline(int $ajuanId): array
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@getDetailTimeline] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function formatAjuan(Ajuan $ajuan): array
    {
        $layananNama = $ajuan->layanan ? $ajuan->layanan->layanan_nama : $ajuan->ajuan_layanan_kode;
        $pelaporName = $ajuan->pelapor ? ($ajuan->pelapor->fullname ?? $ajuan->pelapor->name ?? 'Unknown') : 'Unknown';
        
        return [
            'id' => $ajuan->ajuan_id,
            'no_regis' => $ajuan->ajuan_no_reg,
            'nama' => $pelaporName,
            'nik' => $ajuan->ajuan_pelapor_nik,
            'jenis_layanan' => $layananNama,
            'kecamatan' => $ajuan->ajuan_kecamatan_name,
            'kode_ajuan' => $ajuan->ajuan_layanan_kode,
            'kode_produk' => null,
            'jalur' => $ajuan->isOnline() ? 'online' : 'offline',
            'pelapor' => $ajuan->ajuan_pelapor_role_name,
            'status' => $ajuan->ajuan_status,
            'tanggal' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
            'tanggal_parse' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('Y-m-d, H:i') : null,
            'data_ajuan' => $ajuan->getDetailData(),
        ];
    }

    public function exportExcel(array $filters): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $kategori = $filters['status_kategori'] ?? 'all';
            
            $query = Ajuan::query()->with([
                'pelapor', 
                'layanan',
                'lembarKerjas',
                'produks',
                'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
            ]);
            
            $filter = new AjuanFilter();
            $query = $filter->applyMaster($query, $filters);

            $filename = 'export_pengajuan_' . date('Ymd_His') . '.xlsx';
            if ($kategori === 'lembar_kerja') {
                $filename = 'export_lembar_kerja_' . date('Ymd_His') . '.xlsx';
            } elseif ($kategori === 'produk') {
                $filename = 'export_produk_' . date('Ymd_His') . '.xlsx';
            }
            
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PengajuanExport($query, $kategori), $filename);
        } catch (\Throwable $e) {
            Log::error('[PengajuanService@exportExcel] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
