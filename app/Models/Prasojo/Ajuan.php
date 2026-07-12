<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Ajuan
 *
 * @property int $ajuan_id
 * @property string|null $ajuan_no_reg
 * @property string|null $ajuan_layanan_kode
 * @property int $ajuan_jenis_ajuan_id
 * @property int $ajuan_pelapor_id
 * @property string|null $ajuan_pelapor_nik
 * @property string|null $ajuan_pelapor_kk
 * @property int $ajuan_pelapor_role_id
 * @property string|null $ajuan_pelapor_role_name
 * @property bool $ajuan_is_online
 * @property bool $ajuan_is_mandiri
 * @property string|null $ajuan_status
 * @property string|null $ajuan_kecamatan_code
 * @property string|null $ajuan_kecamatan_name
 * @property string|null $ajuan_kelurahan_code
 * @property string|null $ajuan_kelurahan_name
 * @property string|null $ajuan_keterangan
 * @property array|null $ajuan_extra
 * @property array|null $ajuan_data_ajuan
 * @property \Illuminate\Support\Carbon|null $ajuan_create_datetime
 * @property \Illuminate\Support\Carbon|null $ajuan_update_datetime
 *
 * @property-read User|null $pelapor
 * @property-read JenisAjuan|null $jenisAjuan
 * @property-read Layanan|null $layanan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanAktaKelahiran> $aktaKelahiran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanAktaKematian> $aktaKematian
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanDatang> $datang
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanKia> $kia
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanKk> $kk
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanKtpel> $ktpel
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanPindah> $pindah
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanRekamJemput> $rekamJemput
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanUpdateData> $updateData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanReview> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Produk> $produks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LembarKerja> $lembarKerjas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LogAjuanStatus> $logStatuses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Delivery> $deliveries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryItem> $deliveryItems
 */
final class Ajuan extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PENGAJUAN = 'PENGAJUAN';
    public const string STATUS_VERIFIKASI = 'VERIFIKASI';
    public const string STATUS_DIPROSES = 'DIPROSES';
    public const string STATUS_SELESAI = 'SELESAI';
    public const string STATUS_SELESAI_DIPROSES = 'SELESAI DIPROSES';
    public const string STATUS_DITOLAK = 'DITOLAK';
    public const string STATUS_DIKOREKSI = 'DIKOREKSI';
    public const string STATUS_DIBATALKAN = 'DIBATALKAN';
    public const string STATUS_DISETUJUI = 'DISETUJUI';
    public const string STATUS_BELUM_DIVERIFIKASI = 'BELUM DIVERIFIKASI';

    public const array STATUSES = [
        self::STATUS_PENGAJUAN,
        self::STATUS_VERIFIKASI,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
        self::STATUS_SELESAI_DIPROSES,
        self::STATUS_DITOLAK,
        self::STATUS_DIKOREKSI,
        self::STATUS_DIBATALKAN,
        self::STATUS_DISETUJUI,
        self::STATUS_BELUM_DIVERIFIKASI,
    ];

    /**
     * Map of layanan_kode → detail relation method names.
     *
     * @var array<string, string>
     */
    private const array DETAIL_RELATION_MAP = [
        'AKL' => 'aktaKelahiran',
        'AKM' => 'aktaKematian',
        'DTG' => 'datang',
        'KIA' => 'kia',
        'KK'  => 'kk',
        'KTP' => 'ktpel',
        'PND' => 'pindah',
        'RKJ' => 'rekamJemput',
        'UPD' => 'updateData',
    ];

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan';

    protected $primaryKey = 'ajuan_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajuan_no_reg',
        'ajuan_layanan_kode',
        'ajuan_jenis_ajuan_id',
        'ajuan_pelapor_id',
        'ajuan_pelapor_nik',
        'ajuan_pelapor_kk',
        'ajuan_pelapor_role_id',
        'ajuan_pelapor_role_name',
        'ajuan_is_online',
        'ajuan_is_mandiri',
        'ajuan_status',
        'ajuan_kecamatan_code',
        'ajuan_kecamatan_name',
        'ajuan_kelurahan_code',
        'ajuan_kelurahan_name',
        'ajuan_keterangan',
        'ajuan_extra',
        'ajuan_data_ajuan',
        'ajuan_create_datetime',
        'ajuan_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajuan_jenis_ajuan_id'  => 'integer',
        'ajuan_pelapor_id'      => 'integer',
        'ajuan_pelapor_role_id' => 'integer',
        'ajuan_is_online'       => 'boolean',
        'ajuan_is_mandiri'      => 'boolean',
        'ajuan_extra'           => 'array',
        'ajuan_data_ajuan'      => 'array',
        'ajuan_create_datetime' => 'datetime',
        'ajuan_update_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ajuan_pelapor_id', 'id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajuan_jenis_ajuan_id', 'ja_id');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'ajuan_layanan_kode', 'layanan_kode');
    }

    public function aktaKelahiran(): HasMany
    {
        return $this->hasMany(AjuanAktaKelahiran::class, 'ajakel_ajuan_id', 'ajuan_id');
    }

    public function aktaKematian(): HasMany
    {
        return $this->hasMany(AjuanAktaKematian::class, 'ajakem_ajuan_id', 'ajuan_id');
    }

    public function datang(): HasMany
    {
        return $this->hasMany(AjuanDatang::class, 'ajd_ajuan_id', 'ajuan_id');
    }

    public function kia(): HasMany
    {
        return $this->hasMany(AjuanKia::class, 'ajkia_ajuan_id', 'ajuan_id');
    }

    public function kk(): HasMany
    {
        return $this->hasMany(AjuanKk::class, 'ajkk_ajuan_id', 'ajuan_id');
    }

    public function ktpel(): HasMany
    {
        return $this->hasMany(AjuanKtpel::class, 'ajktpel_ajuan_id', 'ajuan_id');
    }

    public function pindah(): HasMany
    {
        return $this->hasMany(AjuanPindah::class, 'ajp_ajuan_id', 'ajuan_id');
    }

    public function rekamJemput(): HasMany
    {
        return $this->hasMany(AjuanRekamJemput::class, 'ajrj_ajuan_id', 'ajuan_id');
    }

    public function updateData(): HasMany
    {
        return $this->hasMany(AjuanUpdateData::class, 'ajud_ajuan_id', 'ajuan_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AjuanReview::class, 'review_ajuan_id', 'ajuan_id');
    }

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'prod_ajuan_id', 'ajuan_id');
    }

    public function lembarKerjas(): HasMany
    {
        return $this->hasMany(LembarKerja::class, 'lk_ajuan_id', 'ajuan_id');
    }

    public function logStatuses(): HasMany
    {
        return $this->hasMany(LogAjuanStatus::class, 'log_ajuan_id', 'ajuan_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_ajuan_id', 'ajuan_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_item_ajuan_id', 'ajuan_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('ajuan_status', $status);
    }

    /**
     * Scope to filter online ajuan.
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('ajuan_is_online', true);
    }

    /**
     * Scope to filter mandiri (self-submitted) ajuan.
     */
    public function scopeMandiri(Builder $query): Builder
    {
        return $query->where('ajuan_is_mandiri', true);
    }

    /**
     * Scope to filter by kecamatan code.
     */
    public function scopeByKecamatan(Builder $query, string $code): Builder
    {
        return $query->where('ajuan_kecamatan_code', $code);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('ajuan_layanan_kode', $kode);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('ajuan_create_datetime');
    }

    /**
     * Scope to filter ajuan that are completed.
     */
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->whereIn('ajuan_status', [self::STATUS_SELESAI, self::STATUS_SELESAI_DIPROSES]);
    }

    /**
     * Scope to filter ajuan that are rejected.
     */
    public function scopeDitolak(Builder $query): Builder
    {
        return $query->where('ajuan_status', self::STATUS_DITOLAK);
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Normalize NIK pelapor.
     */
    protected function ajuanPelaporNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    /**
     * Resolve the detail relationship based on the current layanan_kode.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function getDetailRelation(): ?HasMany
    {
        $method = self::DETAIL_RELATION_MAP[$this->ajuan_layanan_kode] ?? null;

        if ($method !== null && method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    /**
     * Get the first detail data from eager loaded relations without triggering N+1 query.
     */
    public function getDetailData(): ?Model
    {
        $method = self::DETAIL_RELATION_MAP[$this->ajuan_layanan_kode] ?? null;

        if ($method !== null) {
            $relation = $this->{$method};
            return $relation ? $relation->first() : null;
        }

        return null;
    }

    public function isSelesai(): bool
    {
        return in_array($this->ajuan_status, [self::STATUS_SELESAI, self::STATUS_SELESAI_DIPROSES], true);
    }

    public function isDitolak(): bool
    {
        return $this->ajuan_status === self::STATUS_DITOLAK;
    }

    public function isDiproses(): bool
    {
        return $this->ajuan_status === self::STATUS_DIPROSES;
    }

    public function isPengajuan(): bool
    {
        return $this->ajuan_status === self::STATUS_PENGAJUAN;
    }

    public function isDikoreksi(): bool
    {
        return $this->ajuan_status === self::STATUS_DIKOREKSI;
    }

    public function isDibatalkan(): bool
    {
        return $this->ajuan_status === self::STATUS_DIBATALKAN;
    }

    public function isOnline(): bool
    {
        return (bool) $this->ajuan_is_online;
    }

    public function isMandiri(): bool
    {
        return (bool) $this->ajuan_is_mandiri;
    }
}
