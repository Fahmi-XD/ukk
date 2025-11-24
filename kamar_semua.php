<?php
// Asumsikan bootstrap.php menyertakan koneksi ke database dan file lain yang diperlukan.
include 'bootstrap.php';

// Ambil nilai filter dari URL untuk mengisi kembali form
$capacity = $_GET['capacity'] ?? '';
$price = $_GET['price'] ?? '';
$search = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Kamar - LuxStay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://i.pinimg.com/736x/42/b6/8c/42b68cd2490f7a0467234a71b4d4d6fb.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }

        .room-card {
            transition: transform 0.3s;
            margin-bottom: 30px;
            height: 100%;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .room-img {
            height: 250px;
            object-fit: cover;
        }

        .facilities-icons {
            font-size: 1.2rem;
            color: #0d6efd;
        }

        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <header class="page-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Semua Kamar</h1>
            <p class="lead">Temukan kamar yang sesuai dengan kebutuhan dan budget Anda</p>
        </div>
    </header>

    <div class="container mb-5">
        <div class="filter-section shadow-sm mb-5">
            <form action="semua_kamar.php" method="GET" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="capacity" class="form-label">Kapasitas</label>
                        <select class="form-select" id="capacity" name="capacity">
                            <option value="">Semua Kapasitas</option>
                            <option value="1" <?= $capacity == '1' ? 'selected' : '' ?>>1 Orang</option>
                            <option value="2" <?= $capacity == '2' ? 'selected' : '' ?>>2 Orang</option>
                            <option value="3" <?= $capacity == '3' ? 'selected' : '' ?>>3 Orang</option>
                            <option value="4" <?= $capacity == '4' ? 'selected' : '' ?>>4 Orang</option>
                            <option value="5" <?= $capacity == '5' ? 'selected' : '' ?>>5+ Orang</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="price" class="form-label">Harga Maksimum</label>
                        <select class="form-select" id="price" name="price">
                            <option value="">Semua Harga</option>
                            <option value="500000" <?= $price == '500000' ? 'selected' : '' ?>>Dibawah Rp 500.000</option>
                            <option value="1000000" <?= $price == '1000000' ? 'selected' : '' ?>>Dibawah Rp 1.000.000</option>
                            <option value="1500000" <?= $price == '1500000' ? 'selected' : '' ?>>Dibawah Rp 1.500.000</option>
                            <option value="2000000" <?= $price == '2000000' ? 'selected' : '' ?>>Dibawah Rp 2.000.000</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari Kamar</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Nama atau fasilitas kamar..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                        <button class="btn btn-success w-100 mt-2" type="button" onclick="window.location.href = 'kamar_semua.php'">
                            <i class="fas fa-undo me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row" id="room-list">
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Memuat kamar...</p>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>LuxStay</h5>
                    <p>Temukan pengalaman menginap terbaik dengan harga terjangkau dan fasilitas lengkap.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Jl. Hotel Indah No. 123, Kota</p>
                    <p><i class="fas fa-phone me-2"></i> (021) 1234-5678</p>
                    <p><i class="fas fa-envelope me-2"></i> info@hotelbooking.com</p>
                </div>
                <div class="col-md-4">
                    <h5>Ikuti Kami</h5>
                    <div class="d-flex gap-3 fs-4">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2030 LuxStay. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi untuk mengambil data kamar dengan filter
        function fetchRooms(filters) {
            const container = $("#room-list");
            container.html(`
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat kamar...</p>
                </div>
            `);

            // Buat query string dari objek filter
            // $.param(filters) akan mengonversi objek JS menjadi format URL: capacity=2&price=1000000&search=deluxe
            const queryString = $.param(filters);
            const apiUrl = "api/hotel.php?" + queryString;

            $.ajax({
                url: apiUrl,
                method: "GET",
                dataType: "json",
                success: function(response) {
                    container.empty(); // kosongkan isi dulu

                    if (response.status === "success" && response.data.length > 0) {
                        response.data.forEach(function(room) {
                            // Tentukan badge
                            let badge = room.available_rooms > 0 ?
                                `<span class="badge bg-success">Tersedia ${room.available_rooms} kamar</span>` :
                                `<span class="badge bg-danger">Tidak tersedia</span>`;

                            // Format harga
                            let priceFormatted = new Intl.NumberFormat('id-ID').format(parseFloat(room.price_per_night));

                            // Ikon fasilitas (Ditingkatkan untuk ikon yang lebih spesifik)
                            let icons = '';
                            if (room.facilities) {
                                room.facilities.split(",").forEach(facility => {
                                    let icon = 'fa-check';
                                    let f = facility.toLowerCase().trim();

                                    if (f.includes('wifi')) icon = 'fa-wifi';
                                    else if (f.includes('tv') || f.includes('televisi')) icon = 'fa-tv';
                                    else if (f.includes('ac') || f.includes('air')) icon = 'fa-snowflake';
                                    else if (f.includes('sarapan') || f.includes('breakfast')) icon = 'fa-utensils';
                                    else if (f.includes('minibar')) icon = 'fa-wine-glass-alt';
                                    else if (f.includes('bathtub') || f.includes('jacuzzi')) icon = 'fa-bath';
                                    else if (f.includes('tamu') || f.includes('living')) icon = 'fa-couch';
                                    else if (f.includes('kerja') || f.includes('desk')) icon = 'fa-desktop';

                                    icons += `<span class="me-3"><i class="fas ${icon} me-1 text-primary"></i> ${facility.trim()}</span>`;
                                });
                            }

                            // Tentukan gambar
                            let roomImage;
                            if (room.room_image && room.room_image.length > 0) {
                                roomImage = `./uploads/kamar/${room.room_image[0]}`;
                            } else {
                                roomImage = "https://i.pinimg.com/736x/42/b6/8c/42b68cd2490f7a0467234a71b4d4d6fb.jpg";
                            }


                            // Susun card kamar
                            const html = `
                                <div class="col-md-4 mb-4">
                                    <div class="card room-card shadow">
                                        <img src="${roomImage}" class="card-img-top room-img" alt="${room.name}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">${room.name}</h5>
                                                ${badge}
                                            </div>
                                            <p class="card-text">${room.description.substring(0, 100)}...</p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="badge bg-primary rounded-pill">Kapasitas: ${room.capacity} orang</span>
                                                <span class="fw-bold text-primary">Rp ${priceFormatted}/malam</span>
                                            </div>
                                            <div class="facilities-icons mb-3" style="font-size: 0.9rem;">${icons}</div>
                                            <a href="kamar_detail.php?id=${room.id}" class="btn btn-outline-primary w-100">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>`;

                            container.append(html);
                        });
                    } else {
                        container.html(`<div class="col-12 text-center py-5"><p class="text-muted">😢 Tidak ada tipe kamar yang ditemukan dengan kriteria tersebut.</p></div>`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Gagal memuat data:", error);
                    container.html(`<div class='col-12 text-center py-5'><p class='text-center text-danger'>Gagal memuat data kamar.</p></div>`);
                }
            });
        }

        $(document).ready(function() {
            // 1. Ambil parameter dari URL saat halaman dimuat
            const urlParams = new URLSearchParams(window.location.search);
            const initialFilters = {
                // Ambil nilai filter dari URL (jika ada)
                capacity: urlParams.get('capacity') || '',
                price: urlParams.get('price') || '',
                search: urlParams.get('search') || ''
            };

            // 2. Load kamar dengan filter awal
            fetchRooms(initialFilters);

            // 3. Tangani SUBMIT form
            $('#filter-form').on('submit', function(e) {
                e.preventDefault(); // Mencegah form submission default

                // Kumpulkan filter saat ini dari form
                const currentFilters = {
                    capacity: $('#capacity').val(),
                    price: $('#price').val(),
                    search: $('#search').val()
                };

                // Perbarui URL browser agar filter tetap ada saat reload
                const newUrl = window.location.pathname + '?' + $.param(currentFilters);
                window.history.pushState({
                    path: newUrl
                }, '', newUrl);

                // Panggil fungsi pengambilan data dengan filter baru
                fetchRooms(currentFilters);
            });
        });
    </script>
</body>

</html>