<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Hotel Admin (Dummy UI)</title>
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

        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead th {
            background-color: #e9ecef;
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
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Kelola Pengguna</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class='alert alert-success'>Pengguna berhasil diperbarui!</div>
                <div class="row my-4">
                    <h3 class="fs-4 mb-3">Daftar Pengguna dan Admin</h3>

                    <div class="col-md-12 d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-grow-1 me-3">
                            <form method="GET" action="kelola_user.php" class="d-flex w-100">
                                <input type="hidden" name="sort" value="latest">
                                <input type="text" name="search" class="form-control me-2" placeholder="Cari Nama, Email, Telepon, atau Role..." value="">
                                <button type="submit" class="btn btn-outline-primary">Cari</button>
                                <a href="kelola_user.php?sort=latest" class="btn btn-outline-secondary ms-2">Reset</a>
                            </form>
                        </div>
                        <div class="d-flex align-items-center">
                            <form method="GET" action="kelola_user.php" class="me-3">
                                <input type="hidden" name="search" value="">
                                <select name="sort" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="latest" selected>Urutkan: Terbaru</option>
                                    <optgroup label="Nama">
                                        <option value="name_asc">Nama (A-Z)</option>
                                        <option value="name_desc">Nama (Z-A)</option>
                                    </optgroup>
                                    <optgroup label="Role">
                                        <option value="role_asc">Role (Admin dulu)</option>
                                        <option value="role_desc">Role (User dulu)</option>
                                    </optgroup>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table bg-white rounded shadow-sm table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col" width="50">#</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Telepon</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Terdaftar</th>
                                        <th scope="col" width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">1</th>
                                        <td>Admin Utama</td>
                                        <td>admin@hotel.com</td>
                                        <td>081234567890</td>
                                        <td>
                                            <span class="badge bg-danger">Admin</span>
                                        </td>
                                        <td>01 Jan 2024</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info text-white edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="1"
                                                data-name="Admin Utama"
                                                data-email="admin@hotel.com"
                                                data-phone="081234567890"
                                                data-role="admin">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">2</th>
                                        <td>Budi Santoso</td>
                                        <td>budi.s@mail.com</td>
                                        <td>081122334455</td>
                                        <td>
                                            <span class="badge bg-success">User</span>
                                        </td>
                                        <td>05 Jan 2024</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info text-white edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="2"
                                                data-name="Budi Santoso"
                                                data-email="budi.s@mail.com"
                                                data-phone="081122334455"
                                                data-role="user">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="#" class="btn btn-sm btn-danger" onclick="return false;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">3</th>
                                        <td>Citra Dewi</td>
                                        <td>citra.d@mail.com</td>
                                        <td>085566778899</td>
                                        <td>
                                            <span class="badge bg-danger">Admin</span>
                                        </td>
                                        <td>10 Jan 2024</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info text-white edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="3"
                                                data-name="Citra Dewi"
                                                data-email="citra.d@mail.com"
                                                data-phone="085566778899"
                                                data-role="admin">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="#" class="btn btn-sm btn-danger" onclick="return false;">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="#" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit Pengguna (Dummy)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id" value="[ID]">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="[Nama]" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" value="[Email]" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Telepon</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" value="[Telepon]">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Hak Akses</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="user" selected>User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS Toggle Sidebar Dummy (Tidak ada fungsi PHP, hanya interaksi UI dasar)
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function() {
            el.classList.toggle("toggled");
        };

        // JS Populate Edit Modal Dummy (Hanya mengisi data-id, data-name, dll. ke dalam form modal)
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Mengambil nilai dari data-attribute
                const id = this.dataset.id;
                const name = this.dataset.name;
                const email = this.dataset.email;
                const phone = this.dataset.phone;
                const role = this.dataset.role;

                // Mengisi nilai ke dalam elemen modal
                document.getElementById('editUserModalLabel').textContent = 'Edit Pengguna: ' + name;
                document.getElementById('edit_user_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_phone').value = phone;
                document.getElementById('edit_role').value = role;
            });
        });
    </script>
</body>

</html>