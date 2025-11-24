<?php
session_start();
include '../koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- Variabel Konfigurasi & Inisialisasi ---
$user_id = $_SESSION['user_id'];
$message = '';

// Inisialisasi Array Manual untuk Nama Bulan Indonesia
$nama_bulan_indo = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

// Fungsi Pembantu untuk Format Tanggal Indonesia
function format_indo_date($date_str, $nama_bulan_indo)
{
    if (!$date_str) return '-';
    try {
        $timestamp = strtotime($date_str);
        $day = date('d', $timestamp);
        $month_num = date('m', $timestamp);
        $year = date('Y', $timestamp);
        $month_name = $nama_bulan_indo[$month_num];
        return "$day $month_name $year";
    } catch (\Exception $e) {
        return $date_str; // Fallback
    }
}

// Ambil filter dari GET request, default ke rentang bulan saat ini
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

$filter_tanggal_awal = $_GET['tanggal_awal'] ?? $current_month_start;
$filter_tanggal_akhir = $_GET['tanggal_akhir'] ?? $current_month_end;

// Inisialisasi total
$total_pemasukan = 0;
$total_pengeluaran = 0;
$laba_bersih = 0;

$pemasukan_data = [];
$pengeluaran_data = [];

// Format periode untuk ditampilkan
$tanggal_awal_display = format_indo_date($filter_tanggal_awal, $nama_bulan_indo);
$tanggal_akhir_display = format_indo_date($filter_tanggal_akhir, $nama_bulan_indo);
$periode_display = $tanggal_awal_display . ' s/d ' . $tanggal_akhir_display;


// --- FUNGSI PENGAMBILAN DATA (TELAH DISEDERHANAKAN) ---

/**
 * Mengambil data pendapatan booking dalam rentang tanggal tertentu.
 * Filtering berdasarkan tanggal check_in.
 */
function fetch_pendapatan_booking($koneksi, $tanggal_awal, $tanggal_akhir)
{
    $query = "SELECT check_in as tanggal, booking_code as keterangan, total_price as jumlah, status 
              FROM bookings 
              WHERE check_in BETWEEN ? AND ? 
              AND status IN ('confirmed', 'checked_in', 'checked_out', 'paid')
              ORDER BY check_in ASC";
    $data = [];
    if ($stmt = $koneksi->prepare($query)) {
        // Menggunakan "ss" karena tanggal_awal dan tanggal_akhir adalah string (DATE)
        $stmt->bind_param("ss", $tanggal_awal, $tanggal_akhir);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    }
    return $data;
}

/**
 * Mengambil data pengeluaran Variabel (berdasarkan tanggal) dalam rentang tanggal tertentu.
 * Logika Pengeluaran Tetap dihilangkan.
 */
function fetch_pengeluaran($koneksi, $tanggal_awal, $tanggal_akhir)
{
    // Query hanya mengambil data pengeluaran yang tanggalnya berada dalam rentang filter
    $query = "SELECT id, tanggal, keterangan, jumlah, kategori
              FROM pengeluaran 
              WHERE 
                tanggal BETWEEN ? AND ?
              ORDER BY tanggal ASC";
    $data = [];
    $total = 0;
    if ($stmt = $koneksi->prepare($query)) {
        // Hanya 2 parameter yang digunakan (tanggal_awal dan tanggal_akhir)
        $stmt->bind_param("ss", $tanggal_awal, $tanggal_akhir);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $total += $row['jumlah'];
        }
        $stmt->close();
    }
    return ['data' => $data, 'total' => $total];
}

// --- EKSEKUSI LOGIKA ---
$pemasukan_data = fetch_pendapatan_booking($koneksi, $filter_tanggal_awal, $filter_tanggal_akhir);
foreach ($pemasukan_data as $row) {
    $total_pemasukan += $row['jumlah'];
}

$pengeluaran_result = fetch_pengeluaran($koneksi, $filter_tanggal_awal, $filter_tanggal_akhir);
$pengeluaran_data = $pengeluaran_result['data'];
$total_pengeluaran = $pengeluaran_result['total'];

$laba_bersih = $total_pemasukan - $total_pengeluaran;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - <?= $periode_display ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">

    <style>
        .laba-positif {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .laba-negatif {
            background-color: #f8d7da;
            color: #842029;
        }

        .laba-nol {
            background-color: #fff3cd;
            color: #664d03;
        }

        /* --- CSS KHUSUS CETAK (PRINT) --- */
        @media print {

            /* Sembunyikan elemen yang tidak perlu saat cetak */
            .no-print,
            .sidebar,
            .btn,
            form,
            .navbar {
                display: none !important;
            }

            /* Reset Margin & Padding agar full kertas */
            body,
            .content {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
                width: 100% !important;
            }

            /* Pastikan warna background (seperti tabel header) ikut ter-print */
            .card-header,
            .table-primary,
            .table-danger,
            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Hilangkan shadow card agar terlihat flat di kertas */
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            /* Layout Tanda Tangan muncul saat print */
            .signature-section {
                display: flex !important;
                margin-top: 50px;
                page-break-inside: avoid;
            }
        }

        /* Sembunyikan tanda tangan di tampilan layar biasa */
        .signature-section {
            display: none;
        }
    </style>
</head>

<body class="bg-light">

    <div class="no-print">
        <?php include "sidebar.php" ?>
    </div>

    <div class="mt-5 content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-md-12">

                    <div class="d-flex justify-content-end mb-3 no-print">
                        <button onclick="window.print()" class="btn btn-secondary btn-lg shadow">
                            <i class="fas fa-print me-2"></i> Cetak Laporan
                        </button>
                    </div>

                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-success text-white text-center py-3">
                            <h3 class="mb-1 text-uppercase">Laporan Keuangan Hotel</h3>
                            <h5 class="mb-0">Periode: <strong><?= $periode_display ?></strong></h5>
                        </div>
                        <div class="card-body p-4">

                            <div class="mb-4 p-3 border rounded bg-light-subtle no-print">
                                <form method="GET" action="laporan_keuangan.php" class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Tanggal Awal</label>
                                        <input type="date" class="form-control" name="tanggal_awal" value="<?= $filter_tanggal_awal ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Tanggal Akhir</label>
                                        <input type="date" class="form-control" name="tanggal_akhir" value="<?= $filter_tanggal_akhir ?>" required>
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter me-1"></i> Tampilkan
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="row mb-4 text-center">
                                <div class="col-4">
                                    <div class="card border-primary h-100">
                                        <div class="card-body p-2">
                                            <small class="text-primary fw-bold text-uppercase">Total Pemasukan</small>
                                            <h4 class="fw-bold mt-1">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card border-danger h-100">
                                        <div class="card-body p-2">
                                            <small class="text-danger fw-bold text-uppercase">Total Pengeluaran</small>
                                            <h4 class="fw-bold mt-1">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <?php $laba_class = ($laba_bersih > 0) ? 'laba-positif' : (($laba_bersih < 0) ? 'laba-negatif' : 'laba-nol'); ?>
                                    <div class="card h-100 <?= $laba_class ?>">
                                        <div class="card-body p-2">
                                            <small class="fw-bold text-uppercase">Laba Bersih</small>
                                            <h4 class="fw-bold mt-1">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="text-primary border-bottom pb-2 mb-3">1. Rincian Pemasukan (Booking)</h5>
                                <table class="table table-bordered table-sm align-middle w-100">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th width="15%">Tanggal</th>
                                            <th>Kode Booking / Tamu</th>
                                            <th width="15%">Status</th>
                                            <th width="20%">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($pemasukan_data) > 0): ?>
                                            <?php foreach ($pemasukan_data as $data): ?>
                                                <tr>
                                                    <td class="text-center"><?= date('d/m/Y', strtotime($data['tanggal'])) ?></td>
                                                    <td><?= htmlspecialchars($data['keterangan']) ?></td>
                                                    <td class="text-center"><span class="badge bg-success text-white"><?= ucfirst($data['status']) ?></span></td>
                                                    <td class="text-end">Rp <?= number_format($data['jumlah'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center fst-italic text-muted">Tidak ada data.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="3" class="text-end">Total Pemasukan</td>
                                            <td class="text-end">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-4">
                                <h5 class="text-danger border-bottom pb-2 mb-3">2. Rincian Pengeluaran (Variabel)</h5>
                                <table class="table table-bordered table-sm align-middle w-100">
                                    <thead class="table-danger text-center">
                                        <tr>
                                            <th width="15%">Tanggal</th>
                                            <th>Keterangan</th>
                                            <th width="15%">Kategori</th>
                                            <th width="20%">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($pengeluaran_data) > 0): ?>
                                            <?php foreach ($pengeluaran_data as $data): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <?= date('d/m/Y', strtotime($data['tanggal'])) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($data['keterangan']) ?></td>
                                                    <td><?= htmlspecialchars($data['kategori']) ?></td>
                                                    <td class="text-end">Rp <?= number_format($data['jumlah'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center fst-italic text-muted">Tidak ada data.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="3" class="text-end">Total Pengeluaran</td>
                                            <td class="text-end">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="signature-section row mt-5 pt-5">
                                <div class="col-4 text-center">
                                    <p class="mb-5">Mengetahui,<br><strong>Manager Hotel</strong></p>
                                    <br><br>
                                    <p class="text-decoration-underline fw-bold mt-4">( .................................... )</p>
                                </div>
                                <div class="col-4 offset-4 text-center">
                                    <p class="mb-5">
                                        <?= format_indo_date(date('Y-m-d'), $nama_bulan_indo) ?><br>
                                        Dibuat Oleh,<br><strong>Admin Keuangan</strong>
                                    </p>
                                    <br><br>
                                    <p class="text-decoration-underline fw-bold mt-4">( <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> )</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>