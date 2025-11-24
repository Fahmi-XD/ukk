<?php
session_start();
include '../koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Data Fasilitas (Amenities) yang tersedia untuk form
// Anda bisa mengambil ini dari database jika fasilitas disimpan di tabel terpisah
$available_amenities = [
    'WiFi',
    'TV',
    'AC',
    'Minibar',
    'Kamar Mandi Dalam',
    'Sarapan',
    'Ruang Tamu',
    'Meja Kerja',
    'Lounge Access',
    'Bathtub',
    'Balkon',
    'Pemandangan Kota',
    'Pemandangan Laut'
];

// --- LOGIKA CRUD ---

// 1. Tambah Tipe Kamar
if (isset($_POST['add_type'])) {
    $name = trim($_POST['name']);
    $description = $_POST['description'];
    $price = $_POST['price']; // Variabel ini akan masuk ke kolom price_per_night
    $max_guest = $_POST['max_guest']; // Variabel ini akan masuk ke kolom capacity
    // Gabungkan array fasilitas menjadi string yang dipisahkan koma
    $amenities = implode(',', $_POST['amenities'] ?? []); // Variabel ini akan masuk ke kolom facilities

    // Cek Duplikasi Nama
    $check = $koneksi->prepare("SELECT id FROM room_types WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        // PERBAIKAN: Ganti 'price', 'max_guest', 'amenities' dengan 'price_per_night', 'capacity', 'facilities'
        $stmt = $koneksi->prepare("INSERT INTO room_types (name, description, price_per_night, capacity, facilities) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $max_guest, $amenities); // d untuk decimal, i untuk integer, s untuk string
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Tipe kamar '$name' berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal DB: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Nama tipe kamar '$name' sudah ada!</div>";
    }
}

// 2. Edit Tipe Kamar
if (isset($_POST['edit_type'])) {
    $id = $_POST['type_id'];
    $name = trim($_POST['name']);
    $description = $_POST['description'];
    $price = $_POST['price']; // Variabel ini akan masuk ke kolom price_per_night
    $max_guest = $_POST['max_guest']; // Variabel ini akan masuk ke kolom capacity
    $amenities = implode(',', $_POST['amenities'] ?? []); // Variabel ini akan masuk ke kolom facilities

    // Cek Duplikasi Nama (kecuali ID saat ini)
    $check = $koneksi->prepare("SELECT id FROM room_types WHERE name = ? AND id != ?");
    $check->bind_param("si", $name, $id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        // PERBAIKAN: Ganti 'price', 'max_guest', 'amenities' dengan 'price_per_night', 'capacity', 'facilities'
        $stmt = $koneksi->prepare("UPDATE room_types SET name=?, description=?, price_per_night=?, capacity=?, facilities=? WHERE id=?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $max_guest, $amenities, $id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Tipe kamar berhasil diperbarui!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal update.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Nama tipe kamar '$name' sudah digunakan oleh tipe lain!</div>";
    }
}

// 3. Hapus Tipe Kamar
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // PENTING: Cek apakah ada kamar yang menggunakan tipe ini untuk menjaga integritas data (Foreign Key)
    $check_rooms = $koneksi->prepare("SELECT id FROM rooms WHERE room_type_id = ?");
    $check_rooms->bind_param("i", $id);
    $check_rooms->execute();
    if ($check_rooms->get_result()->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Gagal hapus: Tipe kamar ini masih digunakan oleh beberapa kamar! Harap ubah tipe kamar tersebut terlebih dahulu.</div>";
    } else {
        $stmt = $koneksi->prepare("DELETE FROM room_types WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Redirect untuk menghilangkan parameter delete_id dari URL
            header("Location: kelola_type_kamar.php?msg=success_del");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Gagal hapus.</div>";
        }
    }
}

// Menampilkan pesan sukses setelah redirect (untuk operasi Hapus)
if (isset($_GET['msg']) && $_GET['msg'] == 'success_del') {
    $message = "<div class='alert alert-success'>Tipe kamar berhasil dihapus!</div>";
}


// --- FILTER & SORT LOGIC ---

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name_asc'; // Default sort

$where_clause = '';
$params = [];
$param_types = "";

// Filter berdasarkan nama/deskripsi
if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $where_clause = "WHERE name LIKE ? OR description LIKE ?";
    $param_types .= "ss";
    array_push($params, $like, $like);
}

// Order By Logic
$order_by = "ORDER BY name ASC";
switch ($sort_by) {
    case 'name_desc':
        $order_by = "ORDER BY name DESC";
        break;
    case 'price_asc':
        // PERBAIKAN: Ganti price dengan price_per_night
        $order_by = "ORDER BY price_per_night ASC";
        break;
    case 'price_desc':
        // PERBAIKAN: Ganti price dengan price_per_night
        $order_by = "ORDER BY price_per_night DESC";
        break;
    case 'guest_asc':
        // PERBAIKAN: Ganti max_guest dengan capacity
        $order_by = "ORDER BY capacity ASC";
        break;
    case 'guest_desc':
        // PERBAIKAN: Ganti max_guest dengan capacity
        $order_by = "ORDER BY capacity DESC";
        break;
    case 'latest':
        $order_by = "ORDER BY created_at DESC";
        break;
        // name_asc adalah default
}

$sql = "SELECT id, name, price_per_night AS price, capacity AS max_guest, facilities AS amenities, description FROM room_types " . $where_clause . " " . $order_by;

$stmt = $koneksi->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$room_types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tipe Kamar | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="content">
            <div class="container-fluid mt-4">
                <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-bed me-2"></i>Manajemen Tipe Kamar</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                        <i class="fas fa-plus me-1"></i> Tambah Tipe Kamar
                    </button>
                </div>

                <?= $message ?>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold bg-light">
                        <i class="fas fa-search me-1"></i> Filter & Sortir Daftar Tipe
                    </div>
                    <div class="card-body">
                        <form method="GET" action="kelola_type_kamar.php">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Cari (Nama / Deskripsi)</label>
                                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau deskripsi tipe kamar..." value="<?= htmlspecialchars($search_term) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Urutkan Berdasarkan</label>
                                    <select name="sort_by" class="form-select">
                                        <option value="name_asc" <?= $sort_by == 'name_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                                        <option value="name_desc" <?= $sort_by == 'name_desc' ? 'selected' : '' ?>>Nama (Z-A)</option>
                                        <option value="price_asc" <?= $sort_by == 'price_asc' ? 'selected' : '' ?>>Harga (Termurah)</option>
                                        <option value="price_desc" <?= $sort_by == 'price_desc' ? 'selected' : '' ?>>Harga (Termahal)</option>
                                        <option value="guest_asc" <?= $sort_by == 'guest_asc' ? 'selected' : '' ?>>Max Tamu (Terkecil)</option>
                                        <option value="guest_desc" <?= $sort_by == 'guest_desc' ? 'selected' : '' ?>>Max Tamu (Terbesar)</option>
                                        <option value="latest" <?= $sort_by == 'latest' ? 'selected' : '' ?>>Terbaru</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-50"><i class="fas fa-filter"></i> Terapkan</button>
                                    <a href="kelola_type_kamar.php" class="btn btn-outline-secondary w-50"><i class="fas fa-undo"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Tipe</th>
                                        <th>Harga</th>
                                        <th>Max Tamu</th>
                                        <th>Fasilitas Utama</th>
                                        <th style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($room_types)): ?>
                                        <?php foreach ($room_types as $type): ?>
                                            <tr>
                                                <td><?= $type['id'] ?></td>
                                                <td class="fw-bold">
                                                    <?= htmlspecialchars($type['name']) ?><br>
                                                    <small class="text-muted"><?= substr(htmlspecialchars($type['description']), 0, 80) ?>...</small>
                                                </td>
                                                <td><?= 'Rp ' . number_format($type['price'], 0, ',', '.') ?></td>
                                                <td><?= $type['max_guest'] ?> Orang</td>
                                                <td><?= htmlspecialchars(substr(str_replace(',', ', ', $type['amenities']), 0, 80)) ?>...</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info text-white edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editTypeModal"
                                                        data-id="<?= $type['id'] ?>"
                                                        data-name="<?= htmlspecialchars($type['name']) ?>"
                                                        data-description="<?= htmlspecialchars($type['description']) ?>"
                                                        data-price="<?= htmlspecialchars($type['price']) ?>"
                                                        data-max-guest="<?= htmlspecialchars($type['max_guest']) ?>"
                                                        data-amenities='<?= json_encode(explode(',', $type['amenities'])) ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="kelola_type_kamar.php?delete_id=<?= $type['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ANDA YAKIN INGIN MENGHAPUS TIPE KAMAR INI? Pastikan tidak ada kamar yang menggunakan tipe ini!');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada Tipe Kamar yang ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="kelola_type_kamar.php" method="POST">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Tambah Tipe Kamar Baru</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="add_type" value="1">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Tipe Kamar</label>
                                    <input type="text" class="form-control" name="name" required placeholder="Contoh: Deluxe Room">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Jelaskan fitur utama tipe kamar ini." required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Harga (per Malam, cth: 750000.00)</label>
                                        <input type="number" step="any" class="form-control" name="price" required placeholder="Harga">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Maksimal Tamu</label>
                                        <input type="number" class="form-control" name="max_guest" required min="1" placeholder="Jumlah orang">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fasilitas (Amenities)</label>
                                    <div class="row">
                                        <?php foreach ($available_amenities as $amenity): ?>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= htmlspecialchars($amenity) ?>" id="add_amenity_<?= str_replace(' ', '_', $amenity) ?>">
                                                    <label class="form-check-label" for="add_amenity_<?= str_replace(' ', '_', $amenity) ?>">
                                                        <?= htmlspecialchars($amenity) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Tipe Kamar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="kelola_type_kamar.php" method="POST">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Edit Tipe Kamar</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="edit_type" value="1">
                                <input type="hidden" name="type_id" id="edit_type_id">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Tipe Kamar</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi</label>
                                    <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Harga (per Malam)</label>
                                        <input type="number" step="any" class="form-control" name="price" id="edit_price" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Maksimal Tamu</label>
                                        <input type="number" class="form-control" name="max_guest" id="edit_max_guest" required min="1">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fasilitas (Amenities)</label>
                                    <div class="row" id="edit_amenities_list">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-info text-white">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            const amenitiesContainer = document.getElementById('edit_amenities_list');
            // Ambil daftar fasilitas yang tersedia dari PHP
            const availableAmenities = <?= json_encode($available_amenities) ?>;

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 1. Ambil data dari tombol yang ditekan (menggunakan data-*)
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const description = this.dataset.description;
                    const price = this.dataset.price;
                    const maxGuest = this.dataset.maxGuest;
                    // Data amenities di-parse dari JSON string
                    const selectedAmenities = JSON.parse(this.dataset.amenities);

                    // 2. Isi field input di modal edit
                    document.getElementById('edit_type_id').value = id;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_description').value = description;
                    document.getElementById('edit_price').value = price;
                    document.getElementById('edit_max_guest').value = maxGuest;

                    // 3. Reset dan isi ulang daftar checkbox fasilitas
                    amenitiesContainer.innerHTML = '';
                    availableAmenities.forEach(amenity => {
                        const isChecked = selectedAmenities.includes(amenity);
                        const amenityId = 'edit_amenity_' + amenity.replace(/\s/g, '_').replace(/-/g, '_');

                        const colDiv = document.createElement('div');
                        colDiv.className = 'col-md-4';

                        const formCheckDiv = document.createElement('div');
                        formCheckDiv.className = 'form-check';

                        const input = document.createElement('input');
                        input.className = 'form-check-input';
                        input.type = 'checkbox';
                        input.name = 'amenities[]';
                        input.value = amenity;
                        input.id = amenityId;
                        if (isChecked) {
                            input.checked = true;
                        }

                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.htmlFor = amenityId;
                        label.textContent = amenity;

                        formCheckDiv.appendChild(input);
                        formCheckDiv.appendChild(label);
                        colDiv.appendChild(formCheckDiv);
                        amenitiesContainer.appendChild(colDiv);
                    });
                });
            });
        });
    </script>
</body>

</html>