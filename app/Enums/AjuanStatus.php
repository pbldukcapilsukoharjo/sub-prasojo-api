<?php

namespace App\Enums;

class AjuanStatus
{
    public const DIAJUKAN = 'DIAJUKAN';
    public const BELUM_DIVERIFIKASI = 'BELUM_DIVERIFIKASI';
    public const DIVERIFIKASI = 'DIVERIFIKASI';
    public const DIPROSES = 'DIPROSES';
    public const MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';
    public const DISETUJUI = 'DISETUJUI';
    public const DITOLAK = 'DITOLAK';
    public const SELESAI = 'SELESAI';
    public const DIAJUKAN_TTE = 'DIAJUKAN_TTE';
    public const TIDAK_DIPROSES = 'TIDAK_DIPROSES';
    public const SIAP_DOWNLOAD = 'SIAP_DOWNLOAD';
    public const SIAP_DICETAK = 'SIAP_DICETAK';
    public const SUDAH_DICETAK = 'SUDAH_DICETAK';
    public const SIAP_DIAMBIL = 'SIAP_DIAMBIL';

    public static function getStatusSelesai(): array
    {
        return [
            self::SELESAI,
            self::SIAP_DOWNLOAD,
            self::SIAP_DICETAK,
            self::SUDAH_DICETAK,
            self::SIAP_DIAMBIL,
        ];
    }

    public static function getStatusDitolak(): array
    {
        return [
            self::DITOLAK,
            self::TIDAK_DIPROSES,
        ];
    }
}
