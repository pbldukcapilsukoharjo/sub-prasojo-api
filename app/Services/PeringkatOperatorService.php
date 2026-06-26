<?php

namespace App\Services;

use App\Filters\PeringkatOperatorFilter;
use App\Models\Admin;
use App\Models\Ajuan;
use Illuminate\Support\Facades\DB;

class PeringkatOperatorService
{
    const PER_PAGE = 5;

    /**
     * List Peringkat Operator
     */
    public function getAll(array $filters): array
    {
        $page = $filters['page'] ?? 1;

        /*
        |--------------------------------------------------------------------------
        | Card Statistik
        |--------------------------------------------------------------------------
        */

        $totalLayanan = Ajuan::count();

        /*
        |--------------------------------------------------------------------------
        | Tingkat selesai
        |--------------------------------------------------------------------------
        */

        $totalSelesai = Ajuan::whereIn(
            'ajuan_status',
            [
                'SELESAI',
                'DITERIMA',
                'SELESAI_VERIFIKASI'
            ]
        )->count();

        $tingkatLayanan = $totalLayanan > 0
            ? round(($totalSelesai / $totalLayanan) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Durasi rata-rata
        |--------------------------------------------------------------------------
        |
        | sementara dihitung dari create-update.
        | nanti bisa diganti memakai log status.
        |
        */

        $rataDurasi = round(

            Ajuan::selectRaw("
                AVG(
                    TIMESTAMPDIFF(
                        MINUTE,
                        ajuan_create_datetime,
                        ajuan_update_datetime
                    )
                ) as avg_duration
            ")
            ->value('avg_duration') ?? 0,

            1

        );

        /*
        |--------------------------------------------------------------------------
        | Query Operator
        |--------------------------------------------------------------------------
        */

        $query = Admin::query()

            ->select([

                'id',

                'fullname',

                'kelurahan_name',

                'kecamatan_name',

                'create_datetime'

            ])

            ->where('level', 'operator');

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $query = PeringkatOperatorFilter::apply(
            $query,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Jumlah Ajuan
        |--------------------------------------------------------------------------
        */

        $query->selectSub(

            Ajuan::selectRaw('COUNT(*)')

                ->whereColumn(
                    'ajuan.ajuan_pelapor_id',
                    'admin.id'
                ),

            'total_ajuan'

        );

        /*
        |--------------------------------------------------------------------------
        | Sorting Ranking
        |--------------------------------------------------------------------------
        */

        $query->orderByDesc('total_ajuan');

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $operators = $query->paginate(
            self::PER_PAGE,
            ['*'],
            'page',
            $page
        );

        /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        */

        $rank = ($operators->currentPage() - 1)
            * self::PER_PAGE;

        $list = [];

        foreach ($operators as $operator) {

            $rank++;

            $list[] = [

                'id' => $operator->id,

                'peringkat' => $rank,

                'operator' => $operator->fullname,

                'desa' => $operator->kelurahan_name,

                'kecamatan' => $operator->kecamatan_name,

                'jumlah_ajuan' => (int) $operator->total_ajuan

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'total_layanan' => (int) $totalLayanan,

            'rata_rata_durasi' => (float) $rataDurasi,

            'tingkat_layanan' => (float) $tingkatLayanan,

            'list' => $list,

            'meta' => [

                'page' => $operators->currentPage(),

                'per_page' => $operators->perPage(),

                'total' => $operators->total(),

                'total_page' => $operators->lastPage()

            ]

        ];
    }
    /**
     * Detail Operator
     */
    public function detail(int $id): array
    {
        $operator = Admin::query()
            ->select([
                'id',
                'fullname',
                'username',
                'kelurahan_name',
                'kecamatan_name'
            ])
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Total Ajuan
        |--------------------------------------------------------------------------
        */

        $totalAjuan = Ajuan::where(
            'ajuan_pelapor_id',
            $operator->id
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Total Selesai
        |--------------------------------------------------------------------------
        */

        $totalSelesai = Ajuan::where(
            'ajuan_pelapor_id',
            $operator->id
        )
        ->whereIn(
            'ajuan_status',
            [
                'SELESAI',
                'DITERIMA',
                'SELESAI_VERIFIKASI'
            ]
        )
        ->count();

        /*
        |--------------------------------------------------------------------------
        | Persentase Penyelesaian
        |--------------------------------------------------------------------------
        */

        $tingkatSelesai = 0;

        if ($totalAjuan > 0) {

            $tingkatSelesai = round(

                ($totalSelesai / $totalAjuan) * 100,

                1

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Grafik Bulanan
        |--------------------------------------------------------------------------
        */

        $layananPerBulan = $this->getMonthlyService(
            $operator->id
        );

        /*
        |--------------------------------------------------------------------------
        | Riwayat
        |--------------------------------------------------------------------------
        */

        $riwayat = $this->getHistory(
            $operator->id
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'id' => $operator->id,

            'nama' => $operator->fullname,

            'total_ajuan' => (int) $totalAjuan,

            'total_selesai' => (int) $totalSelesai,

            'tingkat_selesai' => (float) $tingkatSelesai,

            'layanan_perbulan' => $layananPerBulan,

            'riwayat_layanan' => $riwayat

        ];
    }
    /**
     * Grafik layanan per bulan
     */
    private function getMonthlyService(int $operatorId): array
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        $result = [];

        foreach ($months as $month => $name) {

            $result[$name] = Ajuan::query()

                ->where('ajuan_pelapor_id', $operatorId)

                ->whereMonth(
                    'ajuan_create_datetime',
                    $month
                )

                ->whereYear(
                    'ajuan_create_datetime',
                    now()->year
                )

                ->count();

        }

        return $result;
    }

    /**
     * Riwayat layanan operator
     */
    private function getHistory(int $operatorId): array
    {
        $rows = Ajuan::query()

            ->select([

                'ajuan_id',

                'ajuan_no_reg',

                'ajuan_layanan_kode',

                'ajuan_kelurahan_name',

                'ajuan_status',

                'ajuan_create_datetime',

                'ajuan_data_ajuan'

            ])

            ->where(
                'ajuan_pelapor_id',
                $operatorId
            )

            ->orderByDesc(
                'ajuan_create_datetime'
            )

            ->limit(20)

            ->get();

        $history = [];

        foreach ($rows as $row) {

            /*
            |--------------------------------------------------------------------------
            | Nama Pemohon
            |--------------------------------------------------------------------------
            |
            | disimpan dalam JSON ajuan_data_ajuan
            |
            */

            $pemohon = '-';

                $data = $row->ajuan_data_ajuan;

                if (is_string($data)) {
                    $data = json_decode($data, true);
                }

                if (is_array($data)) {

                    $pemohon =
                        $data['nama']
                        ?? $data['nama_lengkap']
                        ?? $data['full_name']
                        ?? '-';

                }
            $history[] = [

                'id' => $row->ajuan_id,

                'no_regis' => $row->ajuan_no_reg,

                'pemohon' => $pemohon,

                'kode_ajuan' => $row->ajuan_layanan_kode,

                'desa' => $row->ajuan_kelurahan_name,

                'tanggal' => optional(
                    $row->ajuan_create_datetime
                )->format('d-m-Y'),

                'waktu' => optional(
                    $row->ajuan_create_datetime
                )->format('H:i'),

                'status' => strtoupper(
                    $row->ajuan_status
                )

            ];

        }

        return $history;
    }

        /**
     * Menghitung tingkat penyelesaian (%)
     */
    private function calculateCompletionRate(
        int $totalAjuan,
        int $totalSelesai
    ): float {

        if ($totalAjuan <= 0) {
            return 0;
        }

        return round(
            ($totalSelesai / $totalAjuan) * 100,
            1
        );
    }

    /**
     * Format tanggal menjadi dd-mm-yyyy
     */
    private function formatDate($datetime): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        return \Carbon\Carbon::parse($datetime)
            ->format('d-m-Y');
    }

    /**
     * Format waktu menjadi HH:mm
     */
    private function formatTime($datetime): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        return \Carbon\Carbon::parse($datetime)
            ->format('H:i');
    }

    /**
     * Normalisasi status agar konsisten
     */
    private function formatStatus(?string $status): string
    {
        if (empty($status)) {
            return '-';
        }

        return strtoupper(trim($status));
    }

    /**
     * Nama bulan Indonesia
     */
    private function getMonthNames(): array
    {
        return [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
    }
}