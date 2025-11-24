<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik - Hotel Admin (Dummy UI)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        /* Gaya Kustom Minimal untuk tampilan Admin/Sidebar */
        :root {
            --main-bg: #f8f9fa;
            /* Light background for the whole app */
            --primary-color: #0d6efd;
            /* Bootstrap primary color */
            --secondary-bg: #fff;
            /* Card/table background */
            --sidebar-color: #343a40;
            /* Dark sidebar background */
            --sidebar-text: #adb5bd;
            /* Light gray text for sidebar */
            --sidebar-active: #ffffff;
            /* White text for active item */
        }

        body {
            overflow-x: hidden;
            background-color: var(--main-bg);
        }

        #page-content-wrapper {
            min-width: 100vw;
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
        }

        #wrapper.toggled #page-content-wrapper {
            min-width: 100vw;
        }

        .list-group-item {
            border: none;
            padding: 20px 30px;
            background-color: transparent;
            color: var(--sidebar-text);
        }

        .list-group-item-action:hover,
        .list-group-item.active {
            background-color: #0d6efd;
            color: var(--sidebar-active);
            border-radius: 5px;
            margin: 0 10px;
        }

        .list-group-item.active i {
            color: var(--sidebar-active) !important;
        }

        .primary-text {
            color: var(--primary-color);
        }

        .card-header.bg-primary {
            background-color: #0d6efd !important;
        }

        .card-header.bg-success {
            background-color: #198754 !important;
        }

        .table-dark th {
            background-color: #343a40;
            color: #fff;
        }

        /* Print Styles - Sesuai dengan yang ada di kode asli, untuk simulasi tampilan cetak */
        .print-data {
            display: none;
            margin-top: 15px;
        }

        @media print {
            body {
                background-color: #fff !important;
            }

            #wrapper.toggled #sidebar-wrapper,
            #wrapper #sidebar-wrapper,
            .navbar,
            .btn-print,
            .form-filter,
            .card-header a {
                display: none !important;
            }

            #page-content-wrapper {
                padding: 0 !important;
                margin-left: 0 !important;
                width: 100% !important;
            }

            .container-fluid {
                padding: 0.5cm !important;
            }

            .card {
                border: 1px solid #ccc !important;
                margin-bottom: 20px;
                box-shadow: none !important;
            }

            .card-header,
            .card-footer {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                border-bottom: 1px solid #ccc !important;
                print-color-adjust: exact;
            }

            .table-dark th {
                background-color: #343a40 !important;
                color: #fff !important;
                print-color-adjust: exact;
            }

            .chart-container,
            .nav-tabs {
                display: none !important;
            }

            .print-data {
                display: block !important;
            }

            .no-print {
                display: none !important;
            }

            #print-container {
                display: none;
            }
        }

        /* Responsif */
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }

            #wrapper.toggled #sidebar-wrapper {
                margin-left: -15rem;
            }

            #wrapper.toggled #page-content-wrapper {
                min-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include "../admin/sidebar.php" ?>
        <div id="page-content-wrapper" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4 no-print">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Laporan & Statistik</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class="row my-5">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                            <div>
                                <a href="#" type="button" class="btn btn-success"><i class="fa-solid fa-money-bill-trend-up me-2"></i>Laporan Keuangan</a>
                            </div>
                            <button onclick="window.print()" class="btn btn-primary btn-print"><i class="fas fa-print me-2"></i> Cetak Laporan</button>
                        </div>
                    </div>

                    <div class="col-md-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-chart-bar me-2"></i> Laporan Bulanan (2025)
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs no-print" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings-chart" type="button" role="tab" aria-controls="bookings-chart" aria-selected="true">Jumlah Pesanan</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue-chart" type="button" role="tab" aria-controls="revenue-chart" aria-selected="false">Pendapatan</button>
                                    </li>
                                </ul>

                                <div class="tab-content pt-3 no-print" id="myTabContent">
                                    <div class="tab-pane fade show active" id="bookings-chart" role="tabpanel" aria-labelledby="bookings-tab">
                                        <div style="height: 300px; background-color: #f0f8ff; border: 1px dashed #0d6efd; display: flex; justify-content: center; align-items: center; color: #0d6efd;">
                                            [Placeholder Diagram Batang Jumlah Pesanan Bulanan Tahun 2025]
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="revenue-chart" role="tabpanel" aria-labelledby="revenue-tab">
                                        <div style="height: 300px; background-color: #f0f8ff; border: 1px dashed #198754; display: flex; justify-content: center; align-items: center; color: #198754;">
                                            [Placeholder Diagram Garis Total Pendapatan Bulanan Tahun 2025]
                                        </div>
                                    </div>
                                </div>
                                <div class="print-data">
                                    <h5>Tabel Data Laporan Bulanan Tahun 2025</h5>
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Jumlah Pesanan Sukses</th>
                                                <th>Total Pendapatan (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Jan (2025)</td>
                                                <td>50</td>
                                                <td>50.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Feb (2025)</td>
                                                <td>45</td>
                                                <td>45.500.000</td>
                                            </tr>
                                            <tr>
                                                <td>Mar (2025)</td>
                                                <td>60</td>
                                                <td>62.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Apr (2025)</td>
                                                <td>70</td>
                                                <td>75.100.000</td>
                                            </tr>
                                            <tr>
                                                <td>Mei (2025)</td>
                                                <td>55</td>
                                                <td>58.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Jun (2025)</td>
                                                <td>80</td>
                                                <td>85.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Jul (2025)</td>
                                                <td>75</td>
                                                <td>79.500.000</td>
                                            </tr>
                                            <tr>
                                                <td>Ags (2025)</td>
                                                <td>90</td>
                                                <td>98.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Sep (2025)</td>
                                                <td>65</td>
                                                <td>67.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Okt (2025)</td>
                                                <td>85</td>
                                                <td>89.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Nov (2025)</td>
                                                <td>78</td>
                                                <td>81.500.000</td>
                                            </tr>
                                            <tr>
                                                <td>Des (2025)</td>
                                                <td>95</td>
                                                <td>102.000.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-calendar-alt me-2"></i> Statistik Penjualan Kamar
                            </div>
                            <div class="card-body">
                                <form method="GET" action="#" class="row g-3 align-items-end mb-4 form-filter no-print">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="2025-01-01" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="2025-01-31" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-search me-2"></i>Tampilkan Laporan</button>
                                    </div>
                                </form>
                                <h5 class="mt-4">Statistik Periode 01 Jan 2025 s/d 31 Jan 2025</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light border rounded text-center">
                                            <h4 class="mb-0">150</h4>
                                            <small class="text-muted">Total Pesanan Sukses</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light border rounded text-center">
                                            <h4 class="mb-0">Rp 120.000.000</h4>
                                            <small class="text-muted">Total Pendapatan</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light border rounded text-center">
                                            <h4 class="mb-0">300</h4>
                                            <small class="text-muted">Total Malam Terjual</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipe Kamar</th>
                                                <th>Jml. Pesanan</th>
                                                <th>Jml. Malam Terjual</th>
                                                <th>Pendapatan (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Standard Room</td>
                                                <td>70</td>
                                                <td>150</td>
                                                <td>45.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Deluxe Room</td>
                                                <td>50</td>
                                                <td>100</td>
                                                <td>40.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>Suite Room</td>
                                                <td>30</td>
                                                <td>50</td>
                                                <td>35.000.000</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">Total Keseluruhan:</th>
                                                <th>150</th>
                                                <th>300</th>
                                                <th>Rp 120.000.000</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS Toggle Sidebar Dummy (Untuk interaksi UI dasar)
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function() {
            el.classList.toggle("toggled");
        };

        // JS untuk simulasi tab (Karena ini statis, Bootstrap JS diperlukan)
        // Note: Chart.js library dihilangkan karena chart diganti placeholder statis.
    </script>
</body>

</html>