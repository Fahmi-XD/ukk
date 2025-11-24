<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Contoh UI Saja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        /* CSS KHUSUS UNTUK TAMPILAN LAPORAN */
        /* Warna Laba (Sesuai dengan kode PHP) */
        .laba-positif { background-color: #d1e7dd; color: #0f5132; } /* Hijau Muda */
        .laba-negatif { background-color: #f8d7da; color: #842029; } /* Merah Muda */
        .laba-nol { background-color: #fff3cd; color: #664d03; } /* Kuning Muda */

        /* CSS KHUSUS CETAK (PRINT) - DITINGGALKAN AGAR FITUR PRINT TETAP JALAN */
        @media print {
            /* Sembunyikan elemen navigasi dan filter */
            .no-print, .sidebar, .btn, form, .navbar {
                display: none !important;
            }

            /* Reset Margin & Padding agar full kertas */
            body, .content {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
                width: 100% !important;
            }

            /* Pastikan warna background (seperti tabel header) ikut ter-print */
            .card-header, .table-primary, .table-danger, .badge {
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

        .content {
            margin-left: 250px; /* Offset for sidebar */
            padding: 20px;
        }
        /* Penyesuaian agar tampilan tanpa sidebar terlihat rapi */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body class="bg-light">
    
    <div class="no-print sidebar">
        <?php include "../admin/sidebar.php" ?>
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
                            <h5 class="mb-0">Periode: <strong>November 2025</strong></h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <div class="mb-4 p-3 border rounded bg-light-subtle no-print">
                                <form action="#" class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Filter Bulan</label>
                                        <select class="form-select" name="bulan">
                                            <option value="11" selected>November</option>
                                            <option value="10">Oktober</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Filter Tahun</label>
                                        <select class="form-select" name="tahun">
                                            <option value="2025" selected>2025</option>
                                            <option value="2024">2024</option>
                                        </select>
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
                                            <h4 class="fw-bold mt-1">Rp 15.500.000</h4> </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card border-danger h-100">
                                        <div class="card-body p-2">
                                            <small class="text-danger fw-bold text-uppercase">Total Pengeluaran</small>
                                            <h4 class="fw-bold mt-1">Rp 6.800.000</h4> </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card h-100 laba-positif">
                                        <div class="card-body p-2">
                                            <small class="fw-bold text-uppercase">Laba Bersih</small>
                                            <h4 class="fw-bold mt-1">Rp 8.700.000</h4> </div>
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
                                        <tr>
                                            <td class="text-center">05/11/2025</td>
                                            <td>BC-2511001 (Tamu A)</td>
                                            <td class="text-center"><span class="badge bg-success text-white">Checked_out</span></td>
                                            <td class="text-end">Rp 2.500.000</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">10/11/2025</td>
                                            <td>BC-2511005 (Tamu B)</td>
                                            <td class="text-center"><span class="badge bg-success text-white">Paid</span></td>
                                            <td class="text-end">Rp 4.000.000</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">15/11/2025</td>
                                            <td>BC-2511010 (Tamu C)</td>
                                            <td class="text-center"><span class="badge bg-success text-white">Confirmed</span></td>
                                            <td class="text-end">Rp 9.000.000</td>
                                        </tr>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="3" class="text-end">Total Pemasukan</td>
                                            <td class="text-end">Rp 15.500.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-4">
                                <h5 class="text-danger border-bottom pb-2 mb-3">2. Rincian Pengeluaran</h5>
                                <table class="table table-bordered table-sm align-middle w-100">
                                    <thead class="table-danger text-center">
                                        <tr>
                                            <th width="15%">Tanggal</th>
                                            <th>Keterangan</th>
                                            <th width="15%">Kategori</th>
                                            <th width="15%">Jenis</th>
                                            <th width="20%">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">-</td>
                                            <td>Gaji Karyawan Bulanan (Staf Hotel)</td>
                                            <td>SDM</td>
                                            <td class="text-center">Tetap (Bulanan)</td>
                                            <td class="text-end">Rp 5.000.000</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">03/11/2025</td>
                                            <td>Pembelian Sabun dan Amenities Kamar</td>
                                            <td>Operasional</td>
                                            <td class="text-center">Variabel</td>
                                            <td class="text-end">Rp 800.000</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">20/11/2025</td>
                                            <td>Perbaikan AC Kamar 102</td>
                                            <td>Maintenance</td>
                                            <td class="text-center">Variabel</td>
                                            <td class="text-end">Rp 1.000.000</td>
                                        </tr>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="4" class="text-end">Total Pengeluaran</td>
                                            <td class="text-end">Rp 6.800.000</td>
                                        </tr>
                                    </tbody>
                                </table>
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