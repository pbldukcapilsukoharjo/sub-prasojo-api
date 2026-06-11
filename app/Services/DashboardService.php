<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\DashboardFilter;
use App\Models\Ajuan;
use App\Models\AjuanReview; // Tambahkan import ini
use App\Models\Produk;
use App\Models\User;

final class DashboardService
{
    public function __construct(
        protected DashboardFilter $filter
    ) {
    }

    public function getDashboard(array $filters): array
    {
        return [
            'total_pengajuan' => $this->totalPengajuan($filters),

            'total_selesai' => $this->totalSelesai($filters),

            'total_ditolak' => $this->totalDitolak($filters),

            'label_tamat' => $this->labelTamat($filters),

            'ajuan_bulanan' => $this->ajuanBulanan($filters),

            'peringkat_operator' => $this->peringkatOperator(),

            'distribusi_wilayah' => $this->distribusiWilayah(),

            'ulasan_pengguna' => $this->ulasanPengguna(),

            'kepatuhan_sla' => 92,

            'total_produk' => $this->totalProduk($filters),

            'rata_proses_selesai' => $this->rataProses(),

            'ringkasan_hari_ini' => $this->ringkasanHariIni(),

            'status_proses' => $this->statusProses(),

            'tanggal_diambil' => now()->format('Y-m-d')
        ];
    }

    public function getDistribusiWilayah(array $filters): array
{
    $wilayah = $this->distribusiWilayah();

    return [
        'total_kecamatan' => count($wilayah),
        'total_ajuan_dokumen' => $this->totalPengajuan($filters),
        'rata_rata_ajuan' => count($wilayah)
            ? round(
                $this->totalPengajuan($filters)
                / count($wilayah),
                2
            )
            : 0,
        'tabel' => [
            'wilayah' => $wilayah,
            'meta' => [
                'page' => 1,
                'per_page' => count($wilayah),
                'total' => count($wilayah),
                'total_page' => 1,
            ]
        ]
    ];
}

public function getWaktuRata(array $filters): array
{
    return [
        'rata_rata_waktu_proses' => 8.5,
        'pencapaian_sla' => 92,
        'target_sla' => 6,
        'tabel' => [
            'layanan' => [
                [
                    'id' => 1,
                    'peringkat' => 1,
                    'jenis_ajuan' => 'TOTAL_AJUAN',
                    'jumlah_ajuan' => Ajuan::count(),
                    'rata_rata_waktu' => 3,
                    'status_sla' => 'ON TIME',
                ]
            ],
            'meta' => [
                'page' => 1,
                'per_page' => 1,
                'total' => 1,
                'total_page' => 1,
            ]
        ]
    ];
}

public function getUlasan(array $filters): array
{
    return [
        'rata_rata_bintang' => round(
            (float) AjuanReview::avg('review_rating'),
            1
        ),

        'total_ulasan' => AjuanReview::count(),

        'total_bintang' => [
            '1' => AjuanReview::where('review_rating', 1)->count(),
            '2' => AjuanReview::where('review_rating', 2)->count(),
            '3' => AjuanReview::where('review_rating', 3)->count(),
            '4' => AjuanReview::where('review_rating', 4)->count(),
            '5' => AjuanReview::where('review_rating', 5)->count(),
        ],

        'tabel' => [
            'ulasan' => AjuanReview::query()
                ->latest('review_create_datetime')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->review_id,
                        'nama' => 'Anonim',
                        'tanggal' => optional(
                            $item->review_create_datetime
                        )->format('Y-m-d'),
                        'waktu' => optional(
                            $item->review_create_datetime
                        )->format('H:i'),
                        'bintang' => $item->review_rating,
                        'layanan' => '-',
                        'isi' => $item->review_content,
                    ];
                })
                ->toArray(),

            'meta' => [
                'page' => 1,
                'per_page' => AjuanReview::count(),
                'total' => AjuanReview::count(),
                'total_page' => 1,
            ]
        ]
    ];
}

    public function getPeringkatOperator(array $filters): array
{
    $operator = $this->peringkatOperator();

    return [
        'total_layanan' => count($operator),
        'rata_rata_durasi' => 14.5,
        'tabel' => [
            'operator' => $operator,
            'meta' => [
                'page' => 1,
                'per_page' => count($operator),
                'total' => count($operator),
                'total_page' => 1,
            ]
        ]
    ];
}

    private function totalPengajuan(array $filters): int
    {
        $query = Ajuan::query();

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    // PERBAIKAN 1: totalSelesai()
    private function totalSelesai(array $filters): int
    {
        $query = Ajuan::query()
            ->where('ajuan_status', 'SELESAI'); // Perbaikan: menggunakan 'ajuan_status'

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    // PERBAIKAN 2: totalDitolak()
    private function totalDitolak(array $filters): int
    {
        $query = Ajuan::query()
            ->where('ajuan_status', 'DITOLAK'); // Perbaikan: menggunakan 'ajuan_status'

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    // PERBAIKAN 3: labelTamat()
    private function labelTamat(array $filters): int
    {
        $query = Ajuan::query()
            ->where('ajuan_layanan_kode', 'TAMAT'); // Perbaikan: menggunakan 'ajuan_layanan_kode'

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    private function ajuanBulanan(array $filters): array
    {
        $query = Ajuan::query();

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return [
            'belum_diverifikasi' => (clone $query)->where('ajuan_status', 'BELUM_DIVERIFIKASI')->count(),
            'diverifikasi' => (clone $query)->where('ajuan_status', 'DIVERIFIKASI')->count(),
            'diproses' => (clone $query)->where('ajuan_status', 'DIPROSES')->count(),
            'disetujui' => (clone $query)->where('ajuan_status', 'DISETUJUI')->count(),
            'ditolak' => (clone $query)->where('ajuan_status', 'DITOLAK')->count(),
            'selesai' => (clone $query)->where('ajuan_status', 'SELESAI')->count(),
        ];
    }

    // PERBAIKAN 4: totalProduk()
    private function totalProduk(array $filters): array
    {
        $query = Produk::query();

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return [
            'diajukan_tte' => (clone $query)->where('prod_status', 'DIAJUKAN_TTE')->count(), // Perbaikan: menggunakan 'prod_status'
            'tidak_diproses' => (clone $query)->where('prod_status', 'TIDAK_DIPROSES')->count(),
            'siap_download' => (clone $query)->where('prod_status', 'SIAP_DOWNLOAD')->count(),
            'siap_dicetak' => (clone $query)->where('prod_status', 'SIAP_DICETAK')->count(),
            'sudah_dicetak' => (clone $query)->where('prod_status', 'SUDAH_DICETAK')->count(),
            'siap_diambil' => (clone $query)->where('prod_status', 'SIAP_DIAMBIL')->count(),
        ];
    }

    private function peringkatOperator(): array
    {
        return User::query()
            ->take(10)
            ->get()
            ->map(
                fn ($item, $index) => [
                    'id' => $item->id,
                    'peringkat' => $index + 1,
                    'nama' => $item->fullname,
                    'kinerja_perbulan' => rand(100, 200),
                ]
            )
            ->toArray();
    }

    private function distribusiWilayah(): array
{
    return Ajuan::query()
        ->selectRaw("
            ajuan_kecamatan_name as nama,
            COUNT(*) as total
        ")
        ->whereNotNull('ajuan_kecamatan_name')
        ->groupBy('ajuan_kecamatan_name')
        ->orderByDesc('total')
        ->get()
        ->values()
        ->map(function ($item, $index) {
            return [
                'id' => $index + 1,
                'nama' => $item->nama,
                'value' => (int) $item->total,
            ];
        })
        ->toArray();
}

    // PERBAIKAN 5: ulasanPengguna()
    private function ulasanPengguna(): array
    {
        return [
            'rata_rata' => round(
                (float)AjuanReview::avg('review_rating'), // Perbaikan: menggunakan AjuanReview dan review_rating
                1
            ),

            'jumlah' => AjuanReview::count(), // Perbaikan: menggunakan AjuanReview

            'content' => AjuanReview::latest('review_create_datetime') // Perbaikan: menggunakan review_create_datetime
                ->take(5)
                ->get()
                ->toArray()
        ];
    }

    private function rataProses(): array
    {
        return [
            'kartu_keluarga' => 4.5,
            'ktp_el' => 8.5,
            'akta_kelahiran' => 12.0,
        ];
    }

    private function ringkasanHariIni(): array
    {
        return [
            'ajuan_masuk' => Ajuan::whereDate(
                'ajuan_create_datetime',
                today()
            )->count(),

            'sla' => 92,

            'rata_rata_menit' => 15.3,
        ];
    }

    private function statusProses(): array
    {
        return [
            'diproses' => Ajuan::where('ajuan_status', 'DIPROSES')->count(), // Perbaikan: menggunakan 'ajuan_status'
            'belum_diverifikasi' => Ajuan::where('ajuan_status', 'BELUM_DIVERIFIKASI')->count(),
            'menunggu_konfirmasi' => Ajuan::where('ajuan_status', 'MENUNGGU_KONFIRMASI')->count(),
        ];
    }
}