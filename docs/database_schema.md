# Schema Database Prasojo

`sql
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2026 at 03:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sukoharjokab_prasojo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `kk` varchar(16) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(13) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `level` varchar(30) NOT NULL DEFAULT 'operator' COMMENT 'administrator,admin,operator',
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not active, 1:active',
  `is_verified` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `is_verified_email` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `is_verified_phone` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `kecamatan_code` varchar(20) DEFAULT NULL,
  `kecamatan_name` varchar(100) DEFAULT NULL,
  `kelurahan_code` varchar(20) DEFAULT NULL,
  `kelurahan_name` varchar(100) DEFAULT NULL,
  `dukuh` varchar(80) DEFAULT NULL,
  `rt` varchar(10) DEFAULT NULL,
  `rw` varchar(10) DEFAULT NULL,
  `extra` longtext DEFAULT NULL,
  `fcm` varchar(255) DEFAULT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_auth`
--

CREATE TABLE `admin_auth` (
  `auth_id` bigint(20) UNSIGNED NOT NULL,
  `auth_admin_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `auth_token` varchar(255) DEFAULT NULL,
  `auth_create_datetime` datetime DEFAULT NULL,
  `auth_expire_datetime` datetime DEFAULT NULL,
  `auth_extra` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_role`
--

CREATE TABLE `admin_role` (
  `admin_role_id` bigint(20) UNSIGNED NOT NULL,
  `admin_role_name` varchar(50) DEFAULT NULL,
  `admin_role_access` longtext DEFAULT NULL COMMENT 'list role'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan`
--

CREATE TABLE `ajuan` (
  `ajuan_id` bigint(20) UNSIGNED NOT NULL,
  `ajuan_no_reg` varchar(20) DEFAULT NULL,
  `ajuan_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai sistem',
  `ajuan_jenis_ajuan_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'sesuai master jenis ajuan',
  `ajuan_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajuan_pelapor_nik` varchar(16) DEFAULT NULL,
  `ajuan_pelapor_kk` varchar(16) DEFAULT NULL,
  `ajuan_pelapor_role_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ajuan_pelapor_role_name` varchar(50) DEFAULT NULL,
  `ajuan_is_online` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0 = offline, 1 = online',
  `ajuan_is_mandiri` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT ' 0 = pelapor dengan status multi ajuan, 1 = sendiri',
  `ajuan_status` varchar(30) DEFAULT NULL,
  `ajuan_kecamatan_code` varchar(20) DEFAULT NULL,
  `ajuan_kecamatan_name` varchar(100) DEFAULT NULL,
  `ajuan_kelurahan_code` varchar(20) DEFAULT NULL,
  `ajuan_kelurahan_name` varchar(100) DEFAULT NULL,
  `ajuan_keterangan` longtext DEFAULT NULL,
  `ajuan_extra` longtext DEFAULT NULL,
  `ajuan_data_ajuan` longtext DEFAULT NULL,
  `ajuan_create_datetime` datetime DEFAULT NULL,
  `ajuan_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_akta_kelahiran`
--

CREATE TABLE `ajuan_akta_kelahiran` (
  `ajakel_id` bigint(20) UNSIGNED NOT NULL,
  `ajakel_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajakel_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajakel_nik` varchar(16) DEFAULT NULL,
  `ajakel_nama_bayi` varchar(100) DEFAULT NULL,
  `ajakel_jenis_kelamin` varchar(9) DEFAULT NULL COMMENT 'list:LAKI-LAKI,PEREMPUAN',
  `ajakel_tempat_lahir` varchar(100) DEFAULT NULL,
  `ajakel_tgl_lahir` date DEFAULT NULL,
  `ajakel_tgl_kawin` date DEFAULT NULL,
  `ajakel_anak_ke` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `ajakel_nama_ibu` varchar(100) DEFAULT NULL,
  `ajakel_nama_ayah` varchar(100) DEFAULT NULL,
  `ajakel_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_akta_kematian`
--

CREATE TABLE `ajuan_akta_kematian` (
  `ajakem_id` bigint(20) UNSIGNED NOT NULL,
  `ajakem_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajakem_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajakem_nik` varchar(16) DEFAULT NULL,
  `ajakem_nama_jenazah` varchar(100) DEFAULT NULL,
  `ajakem_tgl_kematian` datetime DEFAULT NULL,
  `ajakem_tempat_kematian` varchar(100) DEFAULT NULL,
  `ajakem_anak_ke` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0',
  `ajakem_nama_ibu` varchar(100) DEFAULT NULL,
  `ajakem_nama_ayah` varchar(100) DEFAULT NULL,
  `ajakem_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_datang`
--

CREATE TABLE `ajuan_datang` (
  `ajd_id` bigint(20) UNSIGNED NOT NULL,
  `ajd_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajd_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajd_nik` varchar(16) DEFAULT NULL,
  `ajd_no_pindah` varchar(50) DEFAULT NULL,
  `ajd_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajd_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_kia`
--

CREATE TABLE `ajuan_kia` (
  `ajkia_id` bigint(20) UNSIGNED NOT NULL,
  `ajkia_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajkia_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajkia_nik` varchar(16) DEFAULT NULL,
  `ajkia_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajkia_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_kk`
--

CREATE TABLE `ajuan_kk` (
  `ajkk_id` bigint(20) UNSIGNED NOT NULL,
  `ajkk_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajkk_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajkk_kk` varchar(16) DEFAULT NULL,
  `ajkk_nama_kepala_keluarga` varchar(100) DEFAULT NULL,
  `ajkk_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_ktpel`
--

CREATE TABLE `ajuan_ktpel` (
  `ajktpel_id` bigint(20) UNSIGNED NOT NULL,
  `ajktpel_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajktpel_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajktpel_nik` varchar(16) DEFAULT NULL,
  `ajktpel_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajktpel_gol_darah` varchar(2) DEFAULT NULL,
  `ajktpel_dokumen` longtext DEFAULT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_pindah`
--

CREATE TABLE `ajuan_pindah` (
  `ajp_id` bigint(20) UNSIGNED NOT NULL,
  `ajp_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajp_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajp_nik` varchar(16) DEFAULT NULL,
  `ajp_kk` varchar(16) DEFAULT NULL,
  `ajp_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajp_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_rekam_jemput`
--

CREATE TABLE `ajuan_rekam_jemput` (
  `ajrj_id` bigint(20) UNSIGNED NOT NULL,
  `ajrj_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajrj_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajrj_nik` varchar(16) DEFAULT NULL,
  `ajrj_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajrj_alasan` longtext DEFAULT NULL,
  `ajrj_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_review`
--

CREATE TABLE `ajuan_review` (
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `review_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `review_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `review_rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `review_content` longtext DEFAULT NULL,
  `review_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ajuan_update_data`
--

CREATE TABLE `ajuan_update_data` (
  `ajud_id` bigint(20) UNSIGNED NOT NULL,
  `ajud_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajud_jenis_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ajud_nik` varchar(16) DEFAULT NULL,
  `ajud_nama_lengkap` varchar(100) DEFAULT NULL,
  `ajud_dokumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `announcement_title` varchar(200) DEFAULT NULL,
  `announcement_author_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `announcement_author_fullname` varchar(100) DEFAULT NULL,
  `announcement_type` varchar(20) DEFAULT NULL COMMENT 'user,admin',
  `announcement_content` longtext DEFAULT NULL,
  `announcement_status` varchar(20) DEFAULT NULL COMMENT 'publish,draft,trash',
  `announcement_extra` longtext DEFAULT NULL,
  `announcement_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bpp`
--

CREATE TABLE `bpp` (
  `bpp_id` bigint(20) UNSIGNED NOT NULL,
  `bpp_no_reg` varchar(20) DEFAULT NULL,
  `bpp_nik` varchar(16) DEFAULT NULL,
  `bpp_nama` varchar(100) DEFAULT NULL,
  `bpp_tempat_lahir` varchar(255) DEFAULT NULL,
  `bpp_tanggal_lahir` date DEFAULT NULL,
  `bpp_tempat_meninggal` varchar(255) DEFAULT NULL,
  `bpp_tanggal_meninggal` date DEFAULT NULL,
  `bpp_alamat` text DEFAULT NULL,
  `bpp_rt` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bpp_rw` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bpp_kecamatan_code` varchar(20) DEFAULT NULL,
  `bpp_kecamatan_name` varchar(100) DEFAULT NULL,
  `bpp_desa_code` varchar(20) DEFAULT NULL,
  `bpp_desa_name` varchar(100) DEFAULT NULL,
  `bpp_tanggal_pemakaman` date DEFAULT NULL,
  `bpp_makam_kecamatan_code` varchar(20) DEFAULT NULL,
  `bpp_makam_kecamatan_name` varchar(100) DEFAULT NULL,
  `bpp_makam_desa_code` varchar(20) DEFAULT NULL,
  `bpp_makam_desa_name` varchar(100) DEFAULT NULL,
  `bpp_makam_nama` varchar(100) DEFAULT NULL,
  `bpp_makam_kode` varchar(20) DEFAULT NULL,
  `bpp_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `bpp_pelapor_nik` varchar(16) DEFAULT NULL,
  `bpp_pelapor_nama` varchar(100) DEFAULT NULL,
  `bpp_keluarga_telp_nama` varchar(100) DEFAULT NULL,
  `bpp_keluarga_telp_no` varchar(13) DEFAULT NULL,
  `bpp_note` longtext DEFAULT NULL,
  `bpp_status` varchar(30) DEFAULT NULL,
  `bpp_extra` longtext DEFAULT NULL,
  `bpp_create_datetime` datetime DEFAULT NULL,
  `bpp_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bpp_tempat_pemakaman`
--

CREATE TABLE `bpp_tempat_pemakaman` (
  `bpptp_id` bigint(20) UNSIGNED NOT NULL,
  `bpptp_jenis` varchar(60) DEFAULT NULL,
  `bpptp_nama` varchar(200) DEFAULT NULL,
  `bpptp_alamat` text DEFAULT NULL,
  `bpptp_kecamatan_code` varchar(20) DEFAULT NULL,
  `bpptp_kecamatan_name` varchar(100) DEFAULT NULL,
  `bpptp_desa_code` varchar(20) DEFAULT NULL,
  `bpptp_desa_name` varchar(100) DEFAULT NULL,
  `bpptp_petugas_nama` varchar(100) DEFAULT NULL,
  `bpptp_petugas_desa_nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bpp_tempat_pemakaman_jenis`
--

CREATE TABLE `bpp_tempat_pemakaman_jenis` (
  `bppj_id` bigint(20) UNSIGNED NOT NULL,
  `bppj_title` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` bigint(20) UNSIGNED NOT NULL,
  `cat_pos` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'position order list',
  `cat_title` varchar(200) DEFAULT NULL,
  `cat_slug` varchar(200) DEFAULT NULL,
  `cat_content` longtext DEFAULT NULL,
  `cat_type` varchar(20) NOT NULL DEFAULT 'blog' COMMENT 'report,blog',
  `cat_image` longtext DEFAULT NULL,
  `cat_extra` longtext DEFAULT NULL,
  `cat_is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0:tidak aktif, 1:aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `config_id` bigint(20) UNSIGNED NOT NULL,
  `config_name` varchar(100) DEFAULT NULL,
  `config_value` longtext DEFAULT NULL,
  `config_autoload` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `delivery_kode` varchar(20) DEFAULT NULL,
  `delivery_resi` varchar(30) DEFAULT NULL,
  `delivery_proses_kode` varchar(20) DEFAULT NULL,
  `delivery_sender` text DEFAULT NULL,
  `delivery_receiver` text DEFAULT NULL,
  `delivery_receiver_name` varchar(255) DEFAULT NULL,
  `delivery_receiver_phone` varchar(20) DEFAULT NULL,
  `delivery_service` text DEFAULT NULL,
  `delivery_status` varchar(30) DEFAULT NULL COMMENT 'REQUEST,DIKOREKSI,DIPROSES,DISORTIR,SELESAI,DITOLAK',
  `delivery_log` longtext DEFAULT NULL,
  `delivery_proses_datetime` datetime DEFAULT NULL,
  `delivery_create_datetime` datetime DEFAULT NULL,
  `delivery_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_item`
--

CREATE TABLE `delivery_item` (
  `delivery_item_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_item_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_item_delivery_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_item_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_item_prod_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `delivery_item_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `delivery_item_layanan_kode` varchar(20) DEFAULT NULL,
  `delivery_item_prod_nomor` varchar(100) DEFAULT NULL,
  `delivery_item_prod_nama` varchar(50) DEFAULT NULL,
  `delivery_item_create_datetime` datetime DEFAULT NULL,
  `delivery_item_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_proses`
--

CREATE TABLE `delivery_proses` (
  `delivery_proses_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_proses_kode` varchar(20) DEFAULT NULL,
  `delivery_proses_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ilokasi_desa`
--

CREATE TABLE `ilokasi_desa` (
  `desa_id` bigint(20) UNSIGNED NOT NULL,
  `desa_kecamatan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `desa_kecamatan_name` varchar(100) DEFAULT NULL,
  `desa_kecamatan_code` varchar(50) DEFAULT NULL,
  `desa_name` varchar(100) DEFAULT NULL,
  `desa_code` varchar(50) DEFAULT NULL,
  `desa_code_bps` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ilokasi_kabupaten`
--

CREATE TABLE `ilokasi_kabupaten` (
  `kabupaten_id` bigint(20) UNSIGNED NOT NULL,
  `kabupaten_provinsi_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `kabupaten_provinsi_name` varchar(100) DEFAULT NULL,
  `kabupaten_provinsi_code` varchar(50) DEFAULT NULL,
  `kabupaten_name` varchar(100) DEFAULT NULL,
  `kabupaten_code` varchar(50) DEFAULT NULL,
  `kabupaten_code_bps` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ilokasi_kecamatan`
--

CREATE TABLE `ilokasi_kecamatan` (
  `kecamatan_id` bigint(20) UNSIGNED NOT NULL,
  `kecamatan_kabupaten_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `kecamatan_kabupaten_name` varchar(100) DEFAULT NULL,
  `kecamatan_kabupaten_code` varchar(50) DEFAULT NULL,
  `kecamatan_name` varchar(100) DEFAULT NULL,
  `kecamatan_code` varchar(50) DEFAULT NULL,
  `kecamatan_code_bps` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ilokasi_provinsi`
--

CREATE TABLE `ilokasi_provinsi` (
  `provinsi_id` bigint(20) UNSIGNED NOT NULL,
  `provinsi_name` varchar(100) DEFAULT NULL,
  `provinsi_code` varchar(50) DEFAULT NULL,
  `provinsi_code_bps` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_ajuan`
--

CREATE TABLE `jenis_ajuan` (
  `ja_id` bigint(20) UNSIGNED NOT NULL,
  `ja_judul` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `layanan_id` bigint(20) UNSIGNED NOT NULL,
  `layanan_pos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `layanan_is_layanan` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `layanan_is_produk` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `layanan_nama` varchar(150) DEFAULT NULL,
  `layanan_desc` longtext DEFAULT NULL,
  `layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `layanan_image` varchar(255) DEFAULT NULL,
  `layanan_extra` longtext DEFAULT NULL,
  `layanan_is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = tidak aktif, 1 = aktif',
  `layanan_jenis_ajuan_id_list` varchar(50) DEFAULT NULL,
  `layanan_create_datetime` datetime DEFAULT NULL,
  `layanan_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan_content`
--

CREATE TABLE `layanan_content` (
  `lc_id` bigint(20) UNSIGNED NOT NULL,
  `lc_author_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `lc_author_fullname` varchar(200) DEFAULT NULL,
  `lc_title` varchar(255) DEFAULT NULL,
  `lc_slug` varchar(255) DEFAULT NULL,
  `lc_type` varchar(20) NOT NULL DEFAULT 'layanan' COMMENT 'layanan',
  `lc_layanan_kode` varchar(3) NOT NULL DEFAULT '' COMMENT 'sesuai kode sistem',
  `lc_status` varchar(20) NOT NULL DEFAULT 'publish' COMMENT 'publish,draft,trash',
  `lc_content` longtext DEFAULT NULL,
  `lc_image` longtext DEFAULT NULL,
  `lc_extra` longtext DEFAULT NULL,
  `lc_create_datetime` datetime DEFAULT NULL,
  `lc_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lembar_kerja`
--

CREATE TABLE `lembar_kerja` (
  `lk_id` bigint(20) UNSIGNED NOT NULL,
  `lk_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `lk_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `lk_jenis_ajuan_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'sesuai master jenis ajuan',
  `lk_from_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `lk_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `lk_is_produk` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = tidak aktif, 1 = aktif',
  `lk_ajuan_is_online` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0 = offline, 1 = online',
  `lk_ajuan_is_mandiri` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT ' 0 = pelapor dengan status multi ajuan, 1 = sendiri',
  `lk_produk_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `lk_pelapor_role_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lk_pelapor_role_name` varchar(50) DEFAULT NULL,
  `lk_status` varchar(30) DEFAULT NULL,
  `lk_create_datetime` datetime DEFAULT NULL,
  `lk_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_ajuan_status`
--

CREATE TABLE `log_ajuan_status` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `log_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `log_status` varchar(30) DEFAULT NULL,
  `log_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `log_note` longtext DEFAULT NULL,
  `log_extra` longtext DEFAULT NULL,
  `log_admin_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_produk_status`
--

CREATE TABLE `log_produk_status` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `log_produk_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `log_status` varchar(30) DEFAULT NULL,
  `log_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `log_admin_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `log_note` longtext DEFAULT NULL,
  `log_extra` longtext DEFAULT NULL,
  `log_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_data_dukung`
--

CREATE TABLE `master_data_dukung` (
  `mdadu_id` bigint(20) UNSIGNED NOT NULL,
  `mdadu_layanan_kode` varchar(3) DEFAULT NULL,
  `mdadu_judul` varchar(255) DEFAULT NULL,
  `mdadu_desc` varchar(255) DEFAULT NULL,
  `mdadu_image` longtext DEFAULT NULL,
  `mdadu_is_required` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = tidak wajib, 1 = wajib',
  `mdadu_extra` longtext DEFAULT NULL,
  `mdadu_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `notification_user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `notification_title` varchar(255) DEFAULT NULL,
  `notification_type` varchar(20) DEFAULT NULL COMMENT 'ajuan',
  `notification_is_read` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:unread, 1:read',
  `notification_extra` longtext DEFAULT NULL,
  `notification_create_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `post_author_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `post_author_fullname` varchar(200) DEFAULT NULL,
  `post_cat_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `post_cat_title` varchar(200) DEFAULT NULL,
  `post_title` varchar(255) DEFAULT NULL,
  `post_slug` varchar(255) DEFAULT NULL,
  `post_type` varchar(20) NOT NULL DEFAULT 'page' COMMENT 'page,blog',
  `post_status` varchar(20) NOT NULL DEFAULT 'publish' COMMENT 'publish,draft,trash',
  `post_content` longtext DEFAULT NULL,
  `post_image` longtext DEFAULT NULL,
  `post_extra` longtext DEFAULT NULL,
  `post_create_datetime` datetime DEFAULT NULL,
  `post_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `prod_id` bigint(20) UNSIGNED NOT NULL,
  `prod_ajuan_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `prod_pelapor_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `prod_ajuan_no_reg` varchar(20) DEFAULT NULL,
  `prod_nama` varchar(100) DEFAULT NULL,
  `prod_nomor` varchar(50) DEFAULT NULL,
  `prod_from_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `prod_layanan_kode` varchar(3) DEFAULT NULL COMMENT 'sesuai kode sistem',
  `prod_status` varchar(30) DEFAULT NULL,
  `prod_url` varchar(255) DEFAULT NULL,
  `prod_extra` longtext DEFAULT NULL,
  `prod_create_datetime` datetime DEFAULT NULL,
  `prod_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site`
--

CREATE TABLE `site` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pos` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'position order list',
  `title` varchar(200) DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'site' COMMENT 'site',
  `image` longtext DEFAULT NULL,
  `extra` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'publish' COMMENT 'publish,draft,trash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `kk` varchar(16) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(13) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `swafoto` longtext DEFAULT NULL,
  `level` varchar(30) NOT NULL DEFAULT 'user' COMMENT 'user,perantara',
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not active, 1:active',
  `is_verified` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `is_verified_email` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `is_verified_phone` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:not verified, 1:verified',
  `is_request_update` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:no, 1:yes',
  `kecamatan_code` varchar(20) DEFAULT NULL,
  `kecamatan_name` varchar(100) DEFAULT NULL,
  `kelurahan_code` varchar(20) DEFAULT NULL,
  `kelurahan_name` varchar(100) DEFAULT NULL,
  `dukuh` varchar(80) DEFAULT NULL,
  `rt` varchar(10) DEFAULT NULL,
  `rw` varchar(10) DEFAULT NULL,
  `extra` longtext DEFAULT NULL,
  `quota` longtext DEFAULT NULL,
  `fcm` varchar(255) DEFAULT NULL,
  `role_kabupaten_name` varchar(100) DEFAULT NULL,
  `role_kabupaten_code` varchar(20) DEFAULT NULL,
  `role_kecamatan_name` varchar(100) DEFAULT NULL,
  `role_kecamatan_code` varchar(20) DEFAULT NULL,
  `role_kelurahan_name` varchar(100) DEFAULT NULL,
  `role_kelurahan_code` varchar(20) DEFAULT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_auth`
--

CREATE TABLE `user_auth` (
  `auth_id` bigint(20) UNSIGNED NOT NULL,
  `auth_user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `auth_token` varchar(255) DEFAULT NULL,
  `auth_create_datetime` datetime DEFAULT NULL,
  `auth_expire_datetime` datetime DEFAULT NULL,
  `auth_extra` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_register_verify_data`
--

CREATE TABLE `user_register_verify_data` (
  `rvd_id` bigint(20) UNSIGNED NOT NULL,
  `rvd_status` varchar(30) NOT NULL DEFAULT 'PENGAJUAN' COMMENT 'PENGAJUAN,BELUM DIVERIFIKASI,DISETUJUI,DITOLAK',
  `rvd_nik` varchar(16) DEFAULT NULL,
  `rvd_fullname` varchar(100) DEFAULT NULL,
  `rvd_kk` varchar(16) DEFAULT NULL,
  `rvd_email` varchar(200) DEFAULT NULL,
  `rvd_phone` varchar(16) DEFAULT NULL,
  `rvd_userdata` longtext DEFAULT NULL,
  `rvd_token` varchar(255) DEFAULT NULL,
  `rvd_note` longtext DEFAULT NULL,
  `rvd_create_datetime` datetime DEFAULT NULL,
  `rvd_update_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

`
