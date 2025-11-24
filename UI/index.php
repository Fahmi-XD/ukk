<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LuxStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-stat {
            border: none;
        }
    </style>
</head>

<body>

    <?php include "../admin/sidebar.php" ?>

    <div class="content">
        <nav class="navbar rounded mb-4 px-4 py-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h2 class="mb-0 fw-semibold">Dashboard</h2>
                <span class="text-muted">Selamat datang, <strong>Admin</strong></span>
            </div>
        </nav>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card card-stat p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">125</h3>
                            <p class="text-muted mb-0">Total Pesanan</p>
                        </div>
                        <div class="icon-box"><i class="fa-solid fa-book fs-4"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">45</h3>
                            <p class="text-muted mb-0">Total Pengguna</p>
                        </div>
                        <div class="icon-box"><i class="fa-solid fa-users fs-4"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">18</h3>
                            <p class="text-muted mb-0">Total Kamar</p>
                        </div>
                        <div class="icon-box"><i class="fa-solid fa-bed fs-4"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">7</h3>
                            <p class="text-muted mb-0">Perlu Konfirmasi</p>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-25 text-warning"><i class="fa-solid fa-hourglass-half fs-4"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">
                Statistik Pesanan Bulanan (Bulan Ini)
            </div>
            <div class="card-body">
                <canvas id="dailyBookingsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Data Dummy
        const daysInMonth = 30; // Anggap bulan ini 30 hari
        const labels = Array.from({
            length: daysInMonth
        }, (_, i) => i + 1); // [1, 2, ..., 30]
        const data = [1, 3, 2, 4, 5, 3, 0, 1, 2, 6, 7, 4, 3, 5, 2, 1, 0, 3, 4, 5, 6, 2, 1, 0, 3, 4, 5, 2, 1, 3];

        const ctx = document.getElementById('dailyBookingsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: data,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Statistik Pesanan Bulanan (Bulan Ini)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Pesanan'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>