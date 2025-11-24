<?php
session_start();
// Pastikan file koneksi.php sudah me-return objek koneksi (misalnya mysqli)
include '../koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// --- Inisialisasi Variabel Filter ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

$where_clauses = [];
$params = [];
$param_types = "";

// --- LOGIKA BARU: Ambil Nama Admin untuk Struk ---
$admin_name = 'Admin'; 
if (isset($_SESSION['user_id'])) {
    $admin_id = $_SESSION['user_id'];
    $admin_query = "SELECT name FROM users WHERE id = ?";
    $stmt_admin = $koneksi->prepare($admin_query);
    if ($stmt_admin) {
        $stmt_admin->bind_param("i", $admin_id);
        $stmt_admin->execute();
        $admin_result = $stmt_admin->get_result();
        $admin_data = $admin_result->fetch_assoc();
        $stmt_admin->close();
        if ($admin_data) {
            $admin_name = $admin_data['name'];
        }
    }
}

// --- Logika Update Status Pesanan ---
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    $room_id = $_POST['room_id'];

    $koneksi->begin_transaction();
    try {
        // 1. Update Status Pesanan
        $stmt_booking = $koneksi->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt_booking->bind_param("si", $new_status, $booking_id);
        if (!$stmt_booking->execute()) throw new Exception("Gagal update status pesanan.");
        $stmt_booking->close();

        // 2. Update Status Kamar
        $room_status = 'available'; 
        if (in_array($new_status, ['checked_in', 'pending', 'paid'])) {
            $room_status = 'booked';
        }
        if (in_array($new_status, ['cancelled', 'checked_out', 'failed'])) {
            $room_status = 'available';
        }

        $stmt_room = $koneksi->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        $stmt_room->bind_param("si", $room_status, $room_id);
        if (!$stmt_room->execute()) throw new Exception("Gagal update status kamar.");
        $stmt_room->close();

        $koneksi->commit();
        $message = "<div class='alert alert-success'>Status pesanan " . htmlspecialchars($_POST['booking_code_display']) . " berhasil diubah menjadi " . htmlspecialchars($new_status) . "!</div>";
    } catch (Exception $e) {
        $koneksi->rollback();
        $message = "<div class='alert alert-danger'>Kesalahan saat mengubah status: " . $e->getMessage() . "</div>";
    }
}

// --- LOGIKA FETCH DATA PESANAN UNTUK CETAK STRUK ---
$print_booking = null;
if (isset($_GET['print_id']) && is_numeric($_GET['print_id'])) {
    $print_id = $_GET['print_id'];
    $print_query = "
        SELECT 
            b.id, b.booking_code, b.check_in, b.check_out, b.total_price, b.status, b.created_at,
            u.name as user_name, u.email as user_email, u.phone as user_phone,
            r.room_number,
            rt.name as room_type_name,
            rt.price_per_night 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.id = ?
    ";
    $stmt_print = $koneksi->prepare($print_query);
    if ($stmt_print) {
        $stmt_print->bind_param("i", $print_id);
        $stmt_print->execute();
        $print_result = $stmt_print->get_result();
        $print_booking = $print_result->fetch_assoc();
        $stmt_print->close();

        if ($print_booking) {
            $check_in_date = new DateTime($print_booking['check_in']);
            $check_out_date = new DateTime($print_booking['check_out']);
            $interval = $check_in_date->diff($check_out_date);
            $print_booking['nights'] = $interval->days;
            $print_booking['formatted_total_price'] = number_format($print_booking['total_price'], 0, ',', '.');
            $print_booking['formatted_price_per_night'] = number_format($print_booking['price_per_night'], 0, ',', '.');
            $print_booking['payment_status'] = strtoupper(str_replace('_', ' ', $print_booking['status']));
            $print_booking['check_in_formatted'] = date('d/m/Y', strtotime($print_booking['check_in']));
            $print_booking['check_out_formatted'] = date('d/m/Y', strtotime($print_booking['check_out']));
        }
    }
}

// ============== LOGIKA PENGHAPUSAN OTOMATIS DAN PERHITUNGAN WAKTU ==============
function calculateExpirationStatus($checkOutDate) {
    $check_out = new DateTime($checkOutDate);
    $expiry_time = $check_out->modify('+1 day');
    $now = new DateTime();

    if ($now > $expiry_time) return ['status' => 'KADALUWARSA', 'is_expired' => true];
    $interval = $now->diff($expiry_time);
    
    if ($interval->days > 0) $res = "$interval->days hari lagi";
    elseif ($interval->h > 0) $res = "$interval->h jam lagi";
    elseif ($interval->i > 0) $res = "$interval->i menit lagi";
    else $res = "Segera berakhir";
    
    return ['status' => $res, 'is_expired' => false];
}

// Hapus pesanan lama (> 7 hari setelah checkout & status selesai/batal)
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$delete_query = "DELETE b, r FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE (b.status = 'checked_out' OR b.status = 'cancelled' OR b.status = 'failed') AND b.check_out < ?";
$stmt_delete = $koneksi->prepare($delete_query);
if ($stmt_delete) {
    $stmt_delete->bind_param("s", $seven_days_ago);
    $stmt_delete->execute();
    $stmt_delete->close();
}

// ==========================================================================
// --- LOGIKA QUERY UTAMA (SEARCH, FILTER, SORT) ---
// ==========================================================================

// 1. Filter Pencarian Teks
if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $where_clauses[] = "(b.booking_code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR r.room_number LIKE ? OR rt.name LIKE ?)";
    $param_types .= "sssss";
    array_push($params, $like, $like, $like, $like, $like);
}

// 2. Filter Status
if (!empty($filter_status) && $filter_status != 'all') {
    $where_clauses[] = "b.status = ?";
    $param_types .= "s";
    $params[] = $filter_status;
}

// 3. Filter Tanggal Check-in (Range)
if (!empty($date_start)) {
    $where_clauses[] = "b.check_in >= ?";
    $param_types .= "s";
    $params[] = $date_start;
}
if (!empty($date_end)) {
    $where_clauses[] = "b.check_in <= ?";
    $param_types .= "s";
    $params[] = $date_end;
}

// 4. Logika Pengurutan (Sorting)
$order_clause = "ORDER BY b.created_at DESC"; // Default
switch ($sort_by) {
    case 'oldest':
        $order_clause = "ORDER BY b.created_at ASC";
        break;
    case 'checkin_asc':
        $order_clause = "ORDER BY b.check_in ASC";
        break;
    case 'checkin_desc':
        $order_clause = "ORDER BY b.check_in DESC";
        break;
    case 'price_high':
        $order_clause = "ORDER BY b.total_price DESC";
        break;
    case 'price_low':
        $order_clause = "ORDER BY b.total_price ASC";
        break;
}

// 5. Membangun Query
$query = "
     SELECT 
            b.id, b.booking_code, b.check_in, b.check_out, b.total_price, b.status, b.created_at, b.room_id,
            u.name as user_name, u.email as user_email,
            r.room_number,
            rt.name as room_type_name
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     JOIN rooms r ON b.room_id = r.id
     JOIN room_types rt ON r.room_type_id = rt.id
";

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
$query .= " " . $order_clause;

// 6. Eksekusi Query
if (!empty($params)) {
    $stmt = $koneksi->prepare($query);
    // Menggunakan referensi untuk bind_param (penting untuk call_user_func_array)
    $bind_params = array($param_types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $bookings_result = $koneksi->query($query);
    $bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
}

// 7. Proses data untuk tampilan (Expired badge)
if (!empty($bookings)) {
    foreach ($bookings as &$booking) {
        if (in_array($booking['status'], ['checked_out', 'cancelled', 'failed'])) {
             $booking['expired_status'] = '<span class="badge bg-secondary">Selesai/Dihapus</span>';
        } else {
             $expiry_data = calculateExpirationStatus($booking['check_out']);
             if ($expiry_data['is_expired']) {
                 $booking['expired_status'] = '<span class="badge bg-danger">EXPIRED</span>';
             } else {
                 $booking['expired_status'] = '<span class="badge bg-success">' . $expiry_data['status'] . '</span>';
             }
        }
    }
    unset($booking);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Hotel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .status-pending { background-color: #ffc107; color: #000; }
        .status-confirmed, .status-paid { background-color: #0d6efd; color: #fff; }
        .status-checked-in { background-color: #198754; color: #fff; }
        .status-checked-out { background-color: #6c757d; color: #fff; }
        .status-cancelled, .status-failed { background-color: #dc3545; color: #fff; }
        
        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area {
                position: absolute; left: 0; top: 0; width: 100%;
                max-width: 80mm; margin: 0 auto; padding: 10px;
                color: #000; font-family: monospace, sans-serif; font-size: 12px;
                display: flex !important; flex-direction: column; align-items: center;
            }
            #wrapper, .navbar, .modal { display: none !important; }
            .print-header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
            .print-detail p { margin: 2px 0; }
            .print-total { margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; text-align: right; width: 100%; }
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                 <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Kelola Pesanan</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <?= $message ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Pencarian</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="kelola_pesanan.php">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Cari</label>
                                    <input type="text" name="search" class="form-control" placeholder="Kode Booking / Nama / Email..." value="<?= htmlspecialchars($search_term) ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="filter_status" class="form-select">
                                        <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua</option>
                                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="paid" <?= $filter_status == 'paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="checked_in" <?= $filter_status == 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                                        <option value="checked_out" <?= $filter_status == 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                                        <option value="cancelled" <?= $filter_status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Urutkan</label>
                                    <select name="sort_by" class="form-select">
                                        <option value="newest" <?= $sort_by == 'newest' ? 'selected' : '' ?>>Terbaru (Dibuat)</option>
                                        <option value="oldest" <?= $sort_by == 'oldest' ? 'selected' : '' ?>>Terlama (Dibuat)</option>
                                        <option value="checkin_asc" <?= $sort_by == 'checkin_asc' ? 'selected' : '' ?>>Check-in Terdekat</option>
                                        <option value="checkin_desc" <?= $sort_by == 'checkin_desc' ? 'selected' : '' ?>>Check-in Terjauh</option>
                                        <option value="price_high" <?= $sort_by == 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                                        <option value="price_low" <?= $sort_by == 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Mulai Check-in</label>
                                    <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sampai</label>
                                    <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
                                </div>

                                <div class="col-md-12 mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Terapkan Filter</button>
                                    <a href="kelola_pesanan.php" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table bg-white rounded shadow-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Kode</th>
                                        <th scope="col">Pelanggan</th>
                                        <th scope="col">Kamar</th>
                                        <th scope="col">Check-in</th>
                                        <th scope="col">Check-out</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aktif</th> 
                                        <th scope="col" width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($bookings)): ?>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td class="fw-bold text-primary"><?= htmlspecialchars($booking['booking_code']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($booking['user_name']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['user_email']) ?></small>
                                                </td>
                                                <td>No. <?= htmlspecialchars($booking['room_number']) ?><br><small><?= htmlspecialchars($booking['room_type_name']) ?></small></td>
                                                <td><?= htmlspecialchars($booking['check_in']) ?></td>
                                                <td><?= htmlspecialchars($booking['check_out']) ?></td>
                                                <td>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></td>
                                                <td>
                                                    <span class="badge status-<?= str_replace('_', '-', htmlspecialchars($booking['status'])) ?>">
                                                        <?= strtoupper(htmlspecialchars($booking['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= $booking['expired_status'] ?></td>
                                                
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-warning text-dark action-btn"
                                                            data-bs-toggle="modal" data-bs-target="#actionModal"
                                                            data-id="<?= $booking['id'] ?>"
                                                            data-code="<?= htmlspecialchars($booking['booking_code']) ?>"
                                                            data-current-status="<?= htmlspecialchars($booking['status']) ?>"
                                                            data-room-id="<?= $booking['room_id'] ?>"
                                                            title="Ubah Status">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <?php if (in_array($booking['status'], ['paid', 'checked_out', 'checked_in'])): ?>
                                                            <button onclick="printStruk(<?= $booking['id'] ?>)" class="btn btn-sm btn-info text-white" title="Cetak Struk">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>Tidak ada pesanan yang sesuai dengan filter Anda.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="kelola_pesanan.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Status Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="modal_booking_id">
                        <input type="hidden" name="room_id" id="modal_room_id">
                        <input type="hidden" name="booking_code_display" id="modal_booking_code_input">
                        
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Kode Booking</small>
                                    <strong id="modal_booking_code" class="text-primary"></strong>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted d-block">Status Saat Ini</small>
                                    <span class="badge" id="modal_current_status_badge"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_status" class="form-label">Pilih Status Baru</label>
                            <select class="form-select" id="new_status" name="new_status" required>
                                <option value="pending">Pending (Menunggu Pembayaran)</option>
                                <option value="paid">Paid (Sudah Dibayar)</option>
                                <option value="checked_in">Checked In (Tamu Masuk)</option>
                                <option value="checked_out">Checked Out (Tamu Keluar)</option>
                                <option value="cancelled">Cancelled (Dibatalkan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="print-area text-center" style="flex-direction: column; align-items: center; display: none;">
        <?php if ($print_booking): ?>
            <div class="print-header">
                <h3>LuxStay</h3>
                <p>Jl. Hotel Indah No. 123</p>
                <p>(021) 1234-5678</p>
            </div>
            <div class="print-detail">
                <p>===================================</p>
                <p>Tanggal: <?= date('d/m/Y H:i') ?></p>
                <p>Admin: <?= htmlspecialchars($admin_name) ?></p>
                <p>Kode: <strong><?= htmlspecialchars($print_booking['booking_code']) ?></strong></p>
                <p>-----------------------------------</p>
                <p>Pelanggan: <?= htmlspecialchars($print_booking['user_name']) ?></p>
                <p>-----------------------------------</p>
                <p>Kamar: <?= htmlspecialchars($print_booking['room_type_name']) ?> (No. <?= htmlspecialchars($print_booking['room_number']) ?>)</p>
                <p>In: <?= $print_booking['check_in_formatted'] ?> | Out: <?= $print_booking['check_out_formatted'] ?></p>
                <p>Durasi: <?= $print_booking['nights'] ?> malam</p>
                <p>-----------------------------------</p>
                <p>Harga: Rp <?= $print_booking['formatted_price_per_night'] ?>/mlm</p>
                <p>Total: Rp <?= $print_booking['formatted_total_price'] ?></p>
                <p>Status: <?= $print_booking['payment_status'] == "PAID" ? "LUNAS" : $print_booking['payment_status'] ?></p>
            </div>
            <div class="print-total">
                <p>===================================</p>
                <p>Total: <strong>Rp <?= $print_booking['formatted_total_price'] ?></strong></p>
            </div>
            <div class="text-center mt-3">
                <p>Terima kasih!</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        toggleButton.onclick = function() { el.classList.toggle("toggled"); };

        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const code = this.dataset.code;
                const status = this.dataset.currentStatus;
                const roomId = this.dataset.roomId;

                document.getElementById('modal_booking_id').value = id;
                document.getElementById('modal_room_id').value = roomId;
                document.getElementById('modal_booking_code').textContent = code;
                document.getElementById('modal_booking_code_input').value = code; 
                
                const statusBadge = document.getElementById('modal_current_status_badge');
                statusBadge.textContent = status.toUpperCase();
                statusBadge.className = 'badge status-' + status.replace('_', '-');
                document.getElementById('new_status').value = status; 
            });
        });

        function printStruk(id) {
            // Simpan parameter filter saat ini agar tidak hilang saat refresh untuk print
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('print_id', id);
            window.location.href = 'kelola_pesanan.php?' + currentParams.toString();
        }

        <?php if ($print_booking): ?>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelector('.print-area').style.display = 'flex';
                window.print();
                setTimeout(() => {
                    // Hapus print_id dari URL tapi pertahankan filter lainnya
                    const url = new URL(window.location.href);
                    url.searchParams.delete('print_id');
                    window.history.pushState('', document.title, url.toString());
                    document.querySelector('.print-area').style.display = 'none';
                }, 500);
            });
        <?php endif; ?>
    </script>
</body>
</html>