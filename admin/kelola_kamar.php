<?php
session_start();
include '../koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$upload_dir = '../uploads/kamar/';

// --- FUNGSI HELPER UPLOAD & DELETE ---
function handle_multi_upload($file_input_name, $upload_dir)
{
    $uploaded_files = [];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (isset($_FILES[$file_input_name]) && is_array($_FILES[$file_input_name]['name'])) {
        $file_count = count($_FILES[$file_input_name]['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES[$file_input_name]['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp_name = $_FILES[$file_input_name]['tmp_name'][$i];
                $file_name = $_FILES[$file_input_name]['name'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (in_array($file_ext, $allowed_ext)) {
                    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    if (move_uploaded_file($file_tmp_name, $destination)) {
                        $uploaded_files[] = $new_file_name;
                    }
                }
            }
        }
    }
    return $uploaded_files;
}

function delete_old_image($file_name, $upload_dir)
{
    $file_list = explode(',', $file_name);
    foreach ($file_list as $f) {
        $f = trim($f);
        if (!empty($f) && $f != 'default.jpg' && file_exists($upload_dir . $f)) {
            unlink($upload_dir . $f);
        }
    }
}

// --- LOGIKA CRUD ---

// 1. Tambah Kamar (SATUAN & MASSAL)
if (isset($_POST['add_room'])) {
    $mode = $_POST['add_mode']; // 'single' atau 'bulk'
    $room_type_id = $_POST['room_type_id'];
    $floor = $_POST['floor'];

    // Upload gambar (berlaku untuk satu atau banyak kamar yang dibuat kali ini)
    $image_names_array = handle_multi_upload('image_files', $upload_dir);
    $image_to_save = empty($image_names_array) ? 'default.jpg' : implode(',', $image_names_array);

    if ($mode == 'single') {
        $room_number = $_POST['room_number'];

        // Cek Duplikasi
        $check = $koneksi->prepare("SELECT id FROM rooms WHERE room_number = ?");
        $check->bind_param("s", $room_number);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            $stmt = $koneksi->prepare("INSERT INTO rooms (room_number, room_type_id, floor, image, status) VALUES (?, ?, ?, ?, 'available')");
            $stmt->bind_param("siss", $room_number, $room_type_id, $floor, $image_to_save);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Kamar $room_number berhasil ditambahkan!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal DB: " . $stmt->error . "</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Nomor kamar $room_number sudah ada!</div>";
        }
    } elseif ($mode == 'bulk') {
        $start_number = (int)$_POST['start_number'];
        $qty = (int)$_POST['qty'];
        $success_count = 0;
        $fail_count = 0;

        $stmt = $koneksi->prepare("INSERT INTO rooms (room_number, room_type_id, floor, image, status) VALUES (?, ?, ?, ?, 'available')");
        $check = $koneksi->prepare("SELECT id FROM rooms WHERE room_number = ?");

        for ($i = 0; $i < $qty; $i++) {
            $current_num = (string)($start_number + $i);

            // Cek dulu
            $check->bind_param("s", $current_num);
            $check->execute();
            if ($check->get_result()->num_rows == 0) {
                $stmt->bind_param("siss", $current_num, $room_type_id, $floor, $image_to_save);
                if ($stmt->execute()) $success_count++;
                else $fail_count++;
            } else {
                $fail_count++;
            }
        }
        $message = "<div class='alert alert-info'>Berhasil membuat $success_count kamar. Gagal/Duplikat: $fail_count.</div>";
    }
}

// 2. Edit Kamar (SATUAN)
if (isset($_POST['edit_room'])) {
    $id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_type_id = $_POST['room_type_id'];
    $floor = $_POST['floor'];
    $status = $_POST['status'];

    $curr_stmt = $koneksi->prepare("SELECT image FROM rooms WHERE id = ?");
    $curr_stmt->bind_param("i", $id);
    $curr_stmt->execute();
    $current_room = $curr_stmt->get_result()->fetch_assoc();

    $image_to_save = $current_room['image'];
    $new_images = handle_multi_upload('edit_image_files', $upload_dir);

    if (!empty($new_images)) {
        $image_to_save = implode(',', $new_images);
        delete_old_image($current_room['image'], $upload_dir);
    }

    $stmt = $koneksi->prepare("UPDATE rooms SET room_number=?, room_type_id=?, floor=?, status=?, image=? WHERE id=?");
    $stmt->bind_param("sisssi", $room_number, $room_type_id, $floor, $status, $image_to_save, $id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Kamar berhasil diperbarui!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Gagal update.</div>";
    }
}

// 3. Edit Massal Berdasarkan Tipe (BARU)
if (isset($_POST['bulk_edit_by_type'])) {
    $target_type_id = $_POST['target_type_id'];
    $new_status = $_POST['bulk_status']; // Opsional
    $new_floor = $_POST['bulk_floor']; // Opsional

    $query_parts = [];
    $params = [];
    $types = "";

    // Update Status jika dipilih
    if ($new_status != 'no_change') {
        $query_parts[] = "status = ?";
        $params[] = $new_status;
        $types .= "s";
    }

    // Update Lantai jika diisi
    if (!empty($new_floor)) {
        $query_parts[] = "floor = ?";
        $params[] = $new_floor;
        $types .= "i";
    }

    // Update Gambar jika diupload
    $new_bulk_images = handle_multi_upload('bulk_image_files', $upload_dir);
    if (!empty($new_bulk_images)) {
        $img_str = implode(',', $new_bulk_images);
        $query_parts[] = "image = ?";
        $params[] = $img_str;
        $types .= "s";

        // Note: Menghapus gambar lama secara massal agak rumit karena tiap kamar mungkin beda gambar.
        // Disini kita hanya menimpa (overwrite). 
    }

    if (!empty($query_parts)) {
        $sql = "UPDATE rooms SET " . implode(', ', $query_parts) . " WHERE room_type_id = ?";
        $params[] = $target_type_id;
        $types .= "i";

        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $message = "<div class='alert alert-success'>Berhasil memperbarui $affected kamar secara massal!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal update massal: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Tidak ada perubahan yang dipilih.</div>";
    }
}

// 4. Hapus Kamar
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $get_img = $koneksi->prepare("SELECT image FROM rooms WHERE id = ?");
    $get_img->bind_param("i", $id);
    $get_img->execute();
    $del_room = $get_img->get_result()->fetch_assoc();

    $stmt = $koneksi->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($del_room) delete_old_image($del_room['image'], $upload_dir);
        header("Location: kelola_kamar.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Gagal hapus.</div>";
    }
}

// --- DATA UNTUK FILTER & FORM ---
$room_types_result = $koneksi->query("SELECT * FROM room_types ORDER BY name ASC");
$all_room_types = $room_types_result->fetch_all(MYSQLI_ASSOC);

$floors_result = $koneksi->query("SELECT DISTINCT floor FROM rooms ORDER BY floor ASC");
$all_floors = [];
while ($row = $floors_result->fetch_assoc()) $all_floors[] = $row['floor'];

// --- FILTER LOGIC ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_floor = isset($_GET['filter_floor']) ? $_GET['filter_floor'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'latest';

$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $where_clauses[] = "(r.room_number LIKE ? OR rt.name LIKE ?)";
    $param_types .= "ss";
    array_push($params, $like, $like);
}
if (!empty($filter_type) && $filter_type != 'all') {
    $where_clauses[] = "r.room_type_id = ?";
    $param_types .= "i";
    $params[] = $filter_type;
}
if (!empty($filter_floor) && $filter_floor != 'all') {
    $where_clauses[] = "r.floor = ?";
    $param_types .= "i";
    $params[] = $filter_floor;
}
if (!empty($filter_status) && $filter_status != 'all') {
    $where_clauses[] = "r.status = ?";
    $param_types .= "s";
    $params[] = $filter_status;
}

$order_clause = "ORDER BY r.id DESC";
if ($sort_by == 'number_asc') $order_clause = "ORDER BY r.room_number ASC";
elseif ($sort_by == 'number_desc') $order_clause = "ORDER BY r.room_number DESC";
elseif ($sort_by == 'floor_asc') $order_clause = "ORDER BY r.floor ASC, r.room_number ASC";

$query = "SELECT r.*, rt.name as room_type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id";
if (!empty($where_clauses)) $query .= " WHERE " . implode(' AND ', $where_clauses);
$query .= " " . $order_clause;

if (!empty($params)) {
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $rooms = $koneksi->query($query)->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Hotel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Kelola Kamar</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <?= $message ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Manajemen Kamar</h5>
                        <div>
                            <button type="button" class="btn btn-outline-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#bulkEditModal">
                                <i class="fas fa-edit me-1"></i> Edit Massal per Tipe
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="fas fa-plus me-1"></i> Tambah Kamar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="kelola_kamar.php">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Cari</label>
                                    <input type="text" name="search" class="form-control" placeholder="No. Kamar / Tipe..." value="<?= htmlspecialchars($search_term) ?>">
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_type" class="form-select">
                                        <option value="all">Semua Tipe</option>
                                        <?php foreach ($all_room_types as $type): ?>
                                            <option value="<?= $type['id'] ?>" <?= $filter_type == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_floor" class="form-select">
                                        <option value="all">Semua Lantai</option>
                                        <?php foreach ($all_floors as $fl): ?>
                                            <option value="<?= $fl ?>" <?= $filter_floor == $fl ? 'selected' : '' ?>>Lantai <?= $fl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_status" class="form-select">
                                        <option value="all">Semua Status</option>
                                        <option value="available" <?= $filter_status == 'available' ? 'selected' : '' ?>>Available</option>
                                        <option value="booked" <?= $filter_status == 'booked' ? 'selected' : '' ?>>Booked</option>
                                        <option value="maintenance" <?= $filter_status == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="sort_by" class="form-select">
                                        <option value="latest" <?= $sort_by == 'latest' ? 'selected' : '' ?>>Terbaru</option>
                                        <option value="number_asc" <?= $sort_by == 'number_asc' ? 'selected' : '' ?>>No. Kamar (A-Z)</option>
                                        <option value="floor_asc" <?= $sort_by == 'floor_asc' ? 'selected' : '' ?>>Lantai (Bawah-Atas)</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table bg-white rounded shadow-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Gambar</th>
                                        <th>No. Kamar</th>
                                        <th>Tipe</th>
                                        <th>Lantai</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rooms)): ?>
                                        <?php $no = 1;
                                        foreach ($rooms as $room):
                                            $images = explode(',', $room['image']);
                                            $first_image = trim($images[0]);
                                            $image_src = "../uploads/kamar/" . htmlspecialchars($first_image);
                                            $status_badge = match ($room['status']) {
                                                'available' => 'bg-success',
                                                'booked' => 'bg-warning text-dark',
                                                'maintenance' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><img src="<?= $image_src ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover; border: 1px solid #eee;"></td>
                                                <td class="fw-bold"><?= htmlspecialchars($room['room_number']) ?></td>
                                                <td><?= htmlspecialchars($room['room_type_name']) ?></td>
                                                <td><?= htmlspecialchars($room['floor']) ?></td>
                                                <td><span class="badge <?= $status_badge ?>"><?= ucfirst($room['status']) ?></span></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-warning edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editRoomModal"
                                                        data-id="<?= $room['id'] ?>"
                                                        data-number="<?= htmlspecialchars($room['room_number']) ?>"
                                                        data-type-id="<?= $room['room_type_id'] ?>"
                                                        data-floor="<?= $room['floor'] ?>"
                                                        data-status="<?= $room['status'] ?>"
                                                        data-image="<?= htmlspecialchars($room['image']) ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="kelola_kamar.php?delete_id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kamar ini?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data.</td>
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

    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="kelola_kamar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kamar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-3" id="addRoomTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-pane" type="button" role="tab" onclick="setMode('single')">Tambah Satuan</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-pane" type="button" role="tab" onclick="setMode('bulk')">Tambah Banyak (Bulk)</button>
                            </li>
                        </ul>

                        <input type="hidden" name="add_mode" id="add_mode" value="single">

                        <div class="row mb-3 bg-light p-2 rounded border mx-1">
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold">Tipe Kamar</label>
                                <select class="form-select" name="room_type_id" required>
                                    <option value="">Pilih Tipe...</option>
                                    <?php foreach ($all_room_types as $type): ?>
                                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold">Lantai</label>
                                <input type="number" class="form-control" name="floor" required min="1" value="1">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Gambar (Default)</label>
                                <input type="file" class="form-control" name="image_files[]" accept="image/*" multiple required>
                                <div class="form-text x-small">Gambar ini akan digunakan untuk semua kamar yang dibuat sekarang.</div>
                            </div>
                        </div>

                        <div class="tab-content" id="addRoomTabContent">
                            <div class="tab-pane fade show active" id="single-pane" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Kamar (Contoh: 101 atau A1)</label>
                                    <input type="text" class="form-control" name="room_number" id="single_room_number">
                                </div>
                            </div>

                            <div class="tab-pane fade" id="bulk-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nomor Awal (Angka)</label>
                                        <input type="number" class="form-control" name="start_number" id="bulk_start" placeholder="Contoh: 101">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jumlah Kamar</label>
                                        <input type="number" class="form-control" name="qty" id="bulk_qty" placeholder="Contoh: 10">
                                    </div>
                                </div>
                                <div class="alert alert-info x-small">
                                    <i class="fas fa-info-circle"></i> Sistem akan membuat nomor kamar berurutan mulai dari <b>Nomor Awal</b> sebanyak <b>Jumlah Kamar</b>. Nomor yang duplikat akan dilewati.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_room" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="kelola_kamar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fas fa-cogs"></i> Edit Massal per Tipe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning x-small">
                            Hati-hati! Perubahan disini akan berdampak ke <b>SEMUA</b> kamar pada tipe yang dipilih.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Tipe Kamar (Target)</label>
                            <select class="form-select" name="target_type_id" required>
                                <option value="">-- Pilih Tipe yang akan diedit --</option>
                                <?php foreach ($all_room_types as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>
                        <h6 class="text-muted small mb-3">Apa yang ingin diubah? (Biarkan kosong jika tidak diubah)</h6>

                        <div class="mb-3">
                            <label class="form-label">Ubah Status Menjadi:</label>
                            <select class="form-select" name="bulk_status">
                                <option value="no_change">-- Tidak Berubah --</option>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ubah Lantai Menjadi:</label>
                            <input type="number" class="form-control" name="bulk_floor" placeholder="Biarkan kosong jika tidak diubah">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ganti Gambar untuk Semua:</label>
                            <input type="file" class="form-control" name="bulk_image_files[]" accept="image/*" multiple>
                            <div class="form-text text-danger x-small">Gambar lama pada kamar tipe ini akan diganti dengan gambar baru ini.</div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="bulk_edit_by_type" class="btn btn-warning" onclick="return confirm('Yakin ingin mengubah data semua kamar dalam tipe ini?');">Terapkan Perubahan Massal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="kelola_kamar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Kamar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="room_id" id="edit_room_id">
                        <input type="hidden" name="old_image" id="edit_old_image">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Kamar</label>
                                <input type="text" class="form-control" id="edit_room_number" name="room_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lantai</label>
                                <input type="number" class="form-control" id="edit_floor" name="floor" required min="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Kamar</label>
                            <select class="form-select" id="edit_room_type_id" name="room_type_id" required>
                                <?php foreach ($all_room_types as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="mb-3 p-2 border rounded bg-light">
                            <label class="form-label small">Ganti Gambar (Opsional)</label>
                            <input type="file" class="form-control form-control-sm" name="edit_image_files[]" accept="image/*" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_room" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        toggleButton.onclick = function() {
            el.classList.toggle("toggled");
        };

        // Fungsi untuk mengatur mode tambah (Single/Bulk)
        function setMode(mode) {
            document.getElementById('add_mode').value = mode;
            if (mode === 'single') {
                document.getElementById('single_room_number').required = true;
                document.getElementById('bulk_start').required = false;
                document.getElementById('bulk_qty').required = false;
            } else {
                document.getElementById('single_room_number').required = false;
                document.getElementById('bulk_start').required = true;
                document.getElementById('bulk_qty').required = true;
            }
        }

        // Isi modal edit satuan
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_room_id').value = this.dataset.id;
                document.getElementById('edit_old_image').value = this.dataset.image;
                document.getElementById('edit_room_number').value = this.dataset.number;
                document.getElementById('edit_room_type_id').value = this.dataset.typeId;
                document.getElementById('edit_floor').value = this.dataset.floor;
                document.getElementById('edit_status').value = this.dataset.status;
            });
        });
    </script>
</body>

</html>