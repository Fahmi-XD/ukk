<?php
session_start();
include 'koneksi.php';
include 'bootstrap.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah ada parameter kode booking
if (!isset($_GET['kode']) || empty($_GET['kode'])) {
    header('Location: riwayat.php');
    exit;
}

$booking_code = $koneksi->real_escape_string($_GET["kode"]);
$user_id = $_SESSION['user_id'];

// ==========================================
// UPDATE QUERY: Menambahkan data dari tabel users
// ==========================================
$query = "SELECT b.*, r.room_number, r.floor, rt.name as room_type, rt.price_per_night, rt.capacity,
          u.name as user_name, u.email, u.phone
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          JOIN room_types rt ON r.room_type_id = rt.id 
          JOIN users u ON b.user_id = u.id 
          WHERE b.booking_code = '$booking_code' AND b.user_id = $user_id";
$result = $koneksi->query($query);

// Cek apakah booking ditemukan
if ($result->num_rows == 0) {
    header('Location: riwayat.php');
    exit;
}

$booking = $result->fetch_assoc();

// Format harga dan tanggal
$formatted_price = number_format($booking['total_price'], 0, ',', '.');
$check_in_date = date('d F Y', strtotime($booking['check_in']));
$check_out_date = date('d F Y', strtotime($booking['check_out']));

// Hitung jumlah malam
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;

// Status booking labels
$status_labels = [
    'pending' => 'Menunggu Konfirmasi',
    'cancelled' => 'Dibatalkan',
    'checked_in' => 'Check-in',
    'checked_out' => 'Check-out',
    'paid' => 'Lunas',
    'failed' => 'Gagal'
];

$booking_status = isset($status_labels[$booking['status']]) ? $status_labels[$booking['status']] : $booking['status'];

// Logic Status Pembayaran
$payment_status_raw = ($booking['status'] == 'paid' || $booking['status'] == 'checked_in' || $booking['status'] == 'checked_out') ? 'paid' : ($booking['status'] == 'failed' ? 'failed' : 'pending');
$payment_status = isset($status_labels[$payment_status_raw]) ? $status_labels[$payment_status_raw] : $payment_status_raw;

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - LuxStay</title>
    <style>
        /* CSS Utama */
        .booking-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 30px;
            margin-bottom: 30px;
            background-color: #fff;
        }

        .booking-header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .booking-body {
            padding: 30px;
        }

        .booking-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .booking-code {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            background-color: #f0f7ff;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .status-badge {
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            color: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-confirmed {
            background-color: #198754;
        }

        .status-cancelled {
            background-color: #dc3545;
        }

        .info-box {
            border-left: 4px solid #0d6efd;
            background-color: #f0f7ff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        /* --- CSS Khusus untuk Print/Cetak --- */
        @media print {
            body {
                background-color: white;
                font-size: 12pt;
            }

            .d-print-none,
            footer,
            .btn,
            .alert,
            .navbar {
                display: none !important;
            }

            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }

            .booking-card {
                box-shadow: none;
                border: 1px solid #ddd;
                margin: 0;
                width: 100%;
            }

            .booking-header,
            .booking-info,
            .status-badge,
            .booking-code {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .col-md-6 {
                width: 50%;
                float: left;
            }

            .row {
                display: flex;
                flex-wrap: wrap;
            }
        }
    </style>
    <script>
        function printStruk() {
            window.print();
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="booking-card">
                    <div class="booking-header">
                        <h1>Detail Pemesanan - LuxStay</h1>
                    </div>

                    <div class="booking-body">
                        <div class="booking-code">
                            Kode Booking: <?= $booking['booking_code'] ?>
                        </div>

                        <div class="info-box d-print-none">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Status Pemesanan:</strong>
                            <span class="status-badge <?= $booking['status'] == 'paid' ? 'status-confirmed' : ($booking['status'] == 'cancelled' ? 'status-cancelled' : 'status-pending') ?>">
                                <?= $booking_status ?>
                            </span>
                            <?php if ($booking['status'] != 'paid'): ?>
                                <p class="mt-2 mb-0">Pemesanan Anda sedang menunggu konfirmasi dari admin.</p>
                            <?php endif; ?>
                        </div>

                        <div class="booking-info">
                            <h3>Informasi Pemesan</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($booking['user_name']) ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Nomor Telepon:</strong> <?= !empty($booking['phone']) ? htmlspecialchars($booking['phone']) : '-' ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="booking-info">
                            <h3>Informasi Kamar</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nomor Kamar:</strong> <?= $booking['room_number'] ?></p>
                                    <p><strong>Tipe Kamar:</strong> <?= $booking['room_type'] ?></p>
                                    <p><strong>Lantai:</strong> <?= $booking['floor'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Kapasitas:</strong> <?= $booking['capacity'] ?> orang</p>
                                    <p><strong>Harga per Malam:</strong> Rp <?= number_format($booking['price_per_night'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="booking-info">
                            <h3>Detail Waktu & Biaya</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Check-in:</strong> <?= $check_in_date ?></p>
                                    <p><strong>Check-out:</strong> <?= $check_out_date ?></p>
                                    <p><strong>Durasi:</strong> <?= $nights ?> malam</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status Pembayaran:</strong>
                                        <span class="status-badge <?= $payment_status_raw == 'paid' ? 'status-confirmed' : ($payment_status_raw == 'failed' ? 'status-cancelled' : 'status-pending') ?>">
                                            <?= $payment_status ?>
                                        </span>
                                    </p>
                                    <p><strong>Total Pembayaran:</strong> <span class="price-tag">Rp <?= $formatted_price ?></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4" style="display: none;" id="print-date">
                            <small>Dicetak pada: <?= date('d F Y H:i') ?></small>
                        </div>
                        <style>
                            @media print {
                                #print-date {
                                    display: block !important;
                                }
                            }
                        </style>

                        <div class="d-grid gap-2 mb-3 d-print-none">
                            <button onclick="printStruk()" class="btn btn-success btn-lg"><i class="fas fa-print me-2"></i> Cetak Bukti Pemesanan</button>
                        </div>

                        <div class="d-grid gap-2 d-print-none">
                            <a href="riwayat.php" class="btn btn-primary btn-lg">Lihat Riwayat Pemesanan</a>
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-5 mt-5 d-print-none">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>LuxStay</h5>
                    <p>Temukan pengalaman menginap terbaik dengan harga terjangkau.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Jl. Hotel Indah No. 123</p>
                </div>
                <div class="col-md-4">
                    <h5>Social Media</h5>
                    <div class="d-flex gap-3 fs-4">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2023 LuxStay. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>