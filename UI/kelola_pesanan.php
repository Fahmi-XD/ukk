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
        /* Gaya Status Badge */
        .status-pending { background-color: #ffc107; color: #000; }
        .status-paid { background-color: #0d6efd; color: #fff; }
        .status-checked-in { background-color: #198754; color: #fff; }
        .status-checked-out { background-color: #6c757d; color: #fff; }
        .status-cancelled, .status-failed { background-color: #dc3545; color: #fff; }
        
        /* Gaya Wrapper dan Sidebar Dummy */
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        #wrapper {
            width: 100%;
            display: flex;
            transition: all 0.3s;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .navbar {
            background-color: white !important;
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Print Styles (Untuk Struk) */
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
        <?php include "../admin/sidebar.php" ?>

        <div id="page-content-wrapper" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Kelola Pesanan</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Pencarian</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="#">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Cari</label>
                                    <input type="text" name="search" class="form-control" placeholder="Kode Booking / Nama / Email..." value="">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="filter_status" class="form-select">
                                        <option value="all" selected>Semua</option>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="checked_out">Checked Out</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Urutkan</label>
                                    <select name="sort_by" class="form-select">
                                        <option value="newest" selected>Terbaru (Dibuat)</option>
                                        <option value="checkin_asc">Check-in Terdekat</option>
                                        <option value="price_high">Harga Tertinggi</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Mulai Check-in</label>
                                    <input type="date" name="date_start" class="form-control" value="">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sampai</label>
                                    <input type="date" name="date_end" class="form-control" value="">
                                </div>

                                <div class="col-md-12 mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Terapkan Filter</button>
                                    <a href="#" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i> Reset</a>
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
                                    <tr>
                                        <td class="fw-bold text-primary">BKG-87654</td>
                                        <td>
                                            Ahmad Zaki<br>
                                            <small class="text-muted">ahmad.z@email.com</small>
                                        </td>
                                        <td>No. 101<br><small>Deluxe Double</small></td>
                                        <td>2025-12-01</td>
                                        <td>2025-12-03</td>
                                        <td>Rp 1.800.000</td>
                                        <td>
                                            <span class="badge status-pending">PENDING</span>
                                        </td>
                                        <td><span class="badge bg-success">1 hari lagi</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning text-dark action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#actionModal"
                                                    data-id="1" data-code="BKG-87654" data-current-status="pending" data-room-id="5"
                                                    title="Ubah Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">BKG-12345</td>
                                        <td>
                                            Siti Rahayu<br>
                                            <small class="text-muted">siti.r@email.com</small>
                                        </td>
                                        <td>No. 205<br><small>Suite King</small></td>
                                        <td>2025-11-20</td>
                                        <td>2025-11-25</td>
                                        <td>Rp 5.500.000</td>
                                        <td>
                                            <span class="badge status-checked-in">CHECKED IN</span>
                                        </td>
                                        <td><span class="badge bg-success">Segera berakhir</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning text-dark action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#actionModal"
                                                    data-id="2" data-code="BKG-12345" data-current-status="checked_in" data-room-id="12"
                                                    title="Ubah Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="printStruk(2)" class="btn btn-sm btn-info text-white" title="Cetak Struk">
                                                     <i class="fas fa-print"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">BKG-99887</td>
                                        <td>
                                            Budi Santoso<br>
                                            <small class="text-muted">budi.s@email.com</small>
                                        </td>
                                        <td>No. 302<br><small>Standard Twin</small></td>
                                        <td>2025-11-15</td>
                                        <td>2025-11-17</td>
                                        <td>Rp 1.000.000</td>
                                        <td>
                                            <span class="badge status-checked-out">CHECKED OUT</span>
                                        </td>
                                        <td><span class="badge bg-secondary">Selesai/Dihapus</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning text-dark action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#actionModal"
                                                    data-id="3" data-code="BKG-99887" data-current-status="checked_out" data-room-id="2"
                                                    title="Ubah Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="printStruk(3)" class="btn btn-sm btn-info text-white" title="Cetak Struk">
                                                     <i class="fas fa-print"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">BKG-2024A</td>
                                        <td>
                                            Dewi Kusuma<br>
                                            <small class="text-muted">dewi.k@email.com</small>
                                        </td>
                                        <td>No. 410<br><small>Suite King</small></td>
                                        <td>2025-11-01</td>
                                        <td>2025-11-05</td>
                                        <td>Rp 4.400.000</td>
                                        <td>
                                            <span class="badge status-cancelled">CANCELLED</span>
                                        </td>
                                        <td><span class="badge bg-secondary">Selesai/Dihapus</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning text-dark action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#actionModal"
                                                    data-id="4" data-code="BKG-2024A" data-current-status="cancelled" data-room-id="15"
                                                    title="Ubah Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
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
                <form action="#" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Status Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="modal_booking_id" value="">
                        <input type="hidden" name="room_id" id="modal_room_id" value="">
                        <input type="hidden" name="booking_code_display" id="modal_booking_code_input" value="">
                        
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Kode Booking</small>
                                    <strong id="modal_booking_code" class="text-primary">BKG-XXX</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted d-block">Status Saat Ini</small>
                                    <span class="badge status-paid" id="modal_current_status_badge">PAID</span>
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
    <div class="print-area text-center" id="print-area-content" style="display: none;">
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data Dummy untuk Simulasi Struk
        const dummyBookingData = {
            1: {
                booking_code: 'BKG-87654', user_name: 'Ahmad Zaki', room_type_name: 'Deluxe Double', room_number: '101', 
                check_in_formatted: '01/12/2025', check_out_formatted: '03/12/2025', nights: 2, 
                formatted_price_per_night: '900.000', formatted_total_price: '1.800.000', payment_status: 'PENDING', admin_name: 'Admin Hotel'
            },
            2: {
                booking_code: 'BKG-12345', user_name: 'Siti Rahayu', room_type_name: 'Suite King', room_number: '205', 
                check_in_formatted: '20/11/2025', check_out_formatted: '25/11/2025', nights: 5, 
                formatted_price_per_night: '1.100.000', formatted_total_price: '5.500.000', payment_status: 'LUNAS', admin_name: 'Admin Hotel'
            },
            3: {
                booking_code: 'BKG-99887', user_name: 'Budi Santoso', room_type_name: 'Standard Twin', room_number: '302', 
                check_in_formatted: '15/11/2025', check_out_formatted: '17/11/2025', nights: 2, 
                formatted_price_per_night: '500.000', formatted_total_price: '1.000.000', payment_status: 'CHECKED OUT', admin_name: 'Admin Hotel'
            }
        };

        // Fungsionalitas Sidebar Toggle (dari kode asli)
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        if (toggleButton) {
            toggleButton.onclick = function() { el.classList.toggle("toggled"); };
        }

        // Fungsionalitas Modal (Mengambil data dari tombol ke modal)
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
                statusBadge.textContent = status.toUpperCase().replace('_', ' ');
                statusBadge.className = 'badge status-' + status.replace('_', '-');
                document.getElementById('new_status').value = status;
            });
        });

        // Fungsionalitas Print Struk (Simulasi)
        function printStruk(id) {
            const booking = dummyBookingData[id];
            if (!booking) {
                alert("Data booking tidak ditemukan.");
                return;
            }

            const now = new Date();
            const dateStr = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
            
            // Build the receipt content
            const printContent = `
                <div class="print-header">
                    <h3>LuxStay</h3>
                    <p>Jl. Hotel Indah No. 123</p>
                    <p>(021) 1234-5678</p>
                </div>
                <div class="print-detail" style="width: 100%;">
                    <p>===================================</p>
                    <p>Tanggal: ${dateStr}</p>
                    <p>Admin: ${booking.admin_name}</p>
                    <p>Kode: <strong>${booking.booking_code}</strong></p>
                    <p>-----------------------------------</p>
                    <p>Pelanggan: ${booking.user_name}</p>
                    <p>-----------------------------------</p>
                    <p>Kamar: ${booking.room_type_name} (No. ${booking.room_number})</p>
                    <p>In: ${booking.check_in_formatted} | Out: ${booking.check_out_formatted}</p>
                    <p>Durasi: ${booking.nights} malam</p>
                    <p>-----------------------------------</p>
                    <p>Harga: Rp ${booking.formatted_price_per_night}/mlm</p>
                    <p>Total: Rp ${booking.formatted_total_price}</p>
                    <p>Status: ${booking.payment_status}</p>
                </div>
                <div class="print-total">
                    <p>===================================</p>
                    <p>Total: <strong>Rp ${booking.formatted_total_price}</strong></p>
                </div>
                <div class="text-center mt-3">
                    <p>Terima kasih!</p>
                </div>
            `;

            const printArea = document.getElementById('print-area-content');
            printArea.innerHTML = printContent;
            
            // Trigger print view
            printArea.style.display = 'flex';
            window.print();
            
            // Hide print view after print dialog closes (simulasi)
            setTimeout(() => {
                printArea.style.display = 'none';
            }, 500);
        }
    </script>
</body>

</html>