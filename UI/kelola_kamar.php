<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Hotel Admin (STATIS UI)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        /* CSS Sederhana untuk Mensimulasikan admin-style.css */
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --white-color: #fff;
            --dark-color: #212529;
            --primary-text: #0d6efd;
        }

        #wrapper {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }

        #page-content-wrapper {
            min-width: 100vw;
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
        }

        #wrapper.toggled #page-content-wrapper {
            min-width: calc(100vw - 15rem);
        }

        .sidebar-heading {
            padding: 1rem 1.25rem;
            font-size: 1.2rem;
        }

        .list-group-item {
            border: none;
            padding: 0.8rem 1.25rem;
            color: #ccc;
            background-color: transparent;
            font-size: 0.95rem;
        }

        .list-group-item:hover,
        .list-group-item.active {
            background-color: var(--primary-color);
            color: var(--white-color);
            border-radius: 5px;
            margin: 0 5px;
        }

        .primary-text {
            color: var(--primary-text);
        }

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
                min-width: 100vw;
            }
        }

        .x-small {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <script>
        const DUMMY_ROOM_TYPES = [{
            id: 1,
            name: 'Standard'
        }, {
            id: 2,
            name: 'Deluxe'
        }, {
            id: 3,
            name: 'Suite'
        }, ];

        const DUMMY_ROOMS = [{
            id: 101,
            room_number: '101',
            room_type_id: 1,
            room_type_name: 'Standard',
            floor: 1,
            status: 'available',
            image: 'kamar_101.jpg,kamar_101_2.jpg'
        }, {
            id: 205,
            room_number: '205',
            room_type_id: 2,
            room_type_name: 'Deluxe',
            floor: 2,
            status: 'booked',
            image: 'kamar_205.jpg'
        }, {
            id: 310,
            room_number: '310',
            room_type_id: 3,
            room_type_name: 'Suite',
            floor: 3,
            status: 'maintenance',
            image: 'kamar_310.jpg'
        }, {
            id: 102,
            room_number: '102',
            room_type_id: 1,
            room_type_name: 'Standard',
            floor: 1,
            status: 'available',
            image: 'default.jpg'
        }, {
            id: 206,
            room_number: '206',
            room_type_id: 2,
            room_type_name: 'Deluxe',
            floor: 2,
            status: 'available',
            image: 'default.jpg'
        }, ];

        const DUMMY_FLOORS = [1, 2, 3];

        // Helper untuk mendapatkan badge class
        function getStatusBadge(status) {
            switch (status) {
                case 'available':
                    return 'bg-success';
                case 'booked':
                    return 'bg-warning text-dark';
                case 'maintenance':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        // Helper untuk mendapatkan jalur gambar (simulasi)
        function getImageUrl(imageString) {
            const firstImage = imageString.split(',')[0].trim();
            // Menggunakan gambar placeholder statis karena tidak ada file di statis UI
            const placeholder = "https://via.placeholder.com/60x40?text=IMG";
            return firstImage === 'default.jpg' || !firstImage ? placeholder : placeholder.replace('IMG', 'KMR');
        }

        // Fungsi render data ke tabel
        function renderRooms() {
            const tbody = document.querySelector('.table tbody');
            tbody.innerHTML = '';
            DUMMY_ROOMS.forEach((room, index) => {
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td><img src="${getImageUrl(room.image)}" class="rounded" style="width: 60px; height: 40px; object-fit: cover; border: 1px solid #eee;"></td>
                        <td class="fw-bold">${room.room_number}</td>
                        <td>${room.room_type_name}</td>
                        <td>${room.floor}</td>
                        <td><span class="badge ${getStatusBadge(room.status)}">${room.status.charAt(0).toUpperCase() + room.status.slice(1)}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-warning edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editRoomModal"
                                data-id="${room.id}"
                                data-number="${room.room_number}"
                                data-type-id="${room.room_type_id}"
                                data-floor="${room.floor}"
                                data-status="${room.status}"
                                data-image="${room.image}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="#" class="btn btn-sm btn-outline-danger" onclick="alert('Hapus kamar ${room.room_number} (STATIS) tidak diimplementasikan.'); return false;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            if (DUMMY_ROOMS.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data.</td></tr>';
            }
        }

        // Fungsi mengisi dropdown (select)
        function populateDropdowns() {
            const typeSelects = document.querySelectorAll('select[name="room_type_id"], select[name="filter_type"], select[name="target_type_id"]');
            typeSelects.forEach(select => {
                const isFilter = select.name === 'filter_type' || select.name === 'target_type_id';
                const currentVal = select.value;
                select.innerHTML = isFilter ? `<option value="${select.name === 'filter_type' ? 'all' : ''}">-- ${isFilter ? (select.name === 'filter_type' ? 'Semua Tipe' : 'Pilih Tipe yang akan diedit') : 'Pilih Tipe...'} --</option>` : '<option value="">Pilih Tipe...</option>';

                DUMMY_ROOM_TYPES.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    if (option.value === currentVal) option.selected = true;
                    select.appendChild(option);
                });
            });

            const floorSelect = document.querySelector('select[name="filter_floor"]');
            if (floorSelect) {
                const currentVal = floorSelect.value;
                floorSelect.innerHTML = '<option value="all">Semua Lantai</option>';
                DUMMY_FLOORS.forEach(floor => {
                    const option = document.createElement('option');
                    option.value = floor;
                    option.textContent = `Lantai ${floor}`;
                    if (option.value === currentVal) option.selected = true;
                    floorSelect.appendChild(option);
                });
            }
        }

        // Event listener untuk tombol edit
        document.addEventListener('DOMContentLoaded', () => {
            renderRooms();
            populateDropdowns();

            document.addEventListener('click', (e) => {
                if (e.target.closest('.edit-btn')) {
                    const button = e.target.closest('.edit-btn');
                    document.getElementById('edit_room_id').value = button.dataset.id;
                    document.getElementById('edit_room_number').value = button.dataset.number;
                    document.getElementById('edit_floor').value = button.dataset.floor;
                    document.getElementById('edit_room_type_id').value = button.dataset.typeId;
                    document.getElementById('edit_status').value = button.dataset.status;
                    document.getElementById('edit_old_image').value = button.dataset.image;
                }
            });
        });
    </script>

    <div class="d-flex" id="wrapper">
        <?php include "../admin/sidebar.php" ?>
        <div id="page-content-wrapper" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Kelola Kamar</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
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
                        <form method="GET" action="#">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Cari</label>
                                    <input type="text" name="search" class="form-control" placeholder="No. Kamar / Tipe..." value="">
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_type" class="form-select">
                                        <option value="all">Semua Tipe</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_floor" class="form-select">
                                        <option value="all">Semua Lantai</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="filter_status" class="form-select">
                                        <option value="all">Semua Status</option>
                                        <option value="available">Available</option>
                                        <option value="booked">Booked</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="sort_by" class="form-select">
                                        <option value="latest">Terbaru</option>
                                        <option value="number_asc">No. Kamar (A-Z)</option>
                                        <option value="floor_asc">Lantai (Bawah-Atas)</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100" onclick="alert('Simulasi: Filter/Cari (STATIS) tidak diimplementasikan.'); return false;"><i class="fas fa-search"></i></button>
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
                <form action="#" onsubmit="alert('Simulasi: Tambah Kamar (STATIS) tidak diimplementasikan.'); return false;" method="POST" enctype="multipart/form-data">
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
                                    <input type="text" class="form-control" name="room_number" id="single_room_number" required>
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
                <form action="#" onsubmit="alert('Simulasi: Edit Massal (STATIS) tidak diimplementasikan.'); return false;" method="POST" enctype="multipart/form-data">
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
                        <button type="submit" name="bulk_edit_by_type" class="btn btn-warning" onclick="return confirm('Yakin ingin mengubah data semua kamar dalam tipe ini? (STATIS)');">Terapkan Perubahan Massal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="#" onsubmit="alert('Simulasi: Edit Kamar Satuan (STATIS) tidak diimplementasikan.'); return false;" method="POST" enctype="multipart/form-data">
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
        // Toggle Sidebar
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        toggleButton.onclick = function() {
            el.classList.toggle("toggled");
        };

        // Fungsi untuk mengatur mode tambah (Single/Bulk) - untuk modal tambah
        function setMode(mode) {
            document.getElementById('add_mode').value = mode;
        }
    </script>
</body>

</html>