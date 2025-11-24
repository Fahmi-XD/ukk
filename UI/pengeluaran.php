<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">Daftar Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        body {
            background-color: #f8f9fa; /* bg-light */
        }
        .content {
            margin-left: 280px; /* Simulasi ruang untuk sidebar */
            padding: 20px;
        }
        .card-header {
            background-color: #0d6efd !important; /* bg-primary */
        }
        .badge.bg-info {
            background-color: #0dcaf0 !important;
        }
        .badge.bg-success {
            background-color: #198754 !important;
        }
        .btn-warning.text-white {
            color: white !important;
        }
    </style>
</head>

<body class="bg-light">

    <?php include "../admin/sidebar.php" ?>
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-md-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i> <span id="header-title">Daftar Pengeluaran</span></h4>
                    </div>
                    <div class="card-body p-4">
                        <div id="message-area">
                            </div>

                        <div id="read-view">
                            <div class="d-flex justify-content-between mb-3 align-items-center">
                                <button onclick="showForm('add')" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Tambah Pengeluaran Baru
                                </button>
                                <div class="alert alert-info py-2 px-3 m-0">
                                    Total Pengeluaran: Rp <span id="total-pengeluaran">20.500.000,00</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Tipe</th>
                                            <th>Tanggal</th>
                                            <th>Kategori</th>
                                            <th>Keterangan</th>
                                            <th>Periode</th>
                                            <th class="text-end">Jumlah (Rp)</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pengeluaran-table-body">
                                        </tbody>
                                </table>
                            </div>
                            </div>

                        <div id="form-view" style="display:none;">
                            <form id="pengeluaran-form">

                                <input type="hidden" id="form-action" value="add">
                                <input type="hidden" id="form-id" value="">

                                <div class="mb-3">
                                    <label for="jenis_pengeluaran" class="form-label fw-bold">Jenis Pengeluaran <span class="text-danger">*</span></label>
                                    <select class="form-select" id="jenis_pengeluaran" name="jenis_pengeluaran" onchange="toggleInputs(this.value)" required>
                                        <option value="variabel">Variabel (Tanggal Tertentu)</option>
                                        <option value="tetap">Tetap (Berkala/Tagihan)</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="tanggal_container">
                                    <label for="tanggal" class="form-label fw-bold">Tanggal Pengeluaran <span class="text-danger" id="tanggal_required">*</span></label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>">
                                    <div class="form-text">Tanggal pengeluaran terjadi. Kosongkan jika merupakan pengeluaran tetap.</div>
                                </div>

                                <div class="mb-3" id="periode_container" style="display:none;">
                                    <label for="periode" class="form-label fw-bold">Periode Pengulangan <span class="text-danger" id="periode_required">*</span></label>
                                    <input type="text" class="form-control" id="periode" name="periode" placeholder="Contoh: Bulanan, Tahunan, Setiap Awal Bulan">
                                    <div class="form-text">Jelaskan frekuensi pengeluaran tetap ini (cth: Bulanan).</div>
                                </div>


                                <div class="mb-3">
                                    <label for="kategori" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="" disabled selected>Pilih Kategori Pengeluaran</option>
                                        <option value="Operasional">Operasional</option>
                                        <option value="Gaji Karyawan">Gaji Karyawan</option>
                                        <option value="Perawatan & Perbaikan">Perawatan & Perbaikan</option>
                                        <option value="Pembelian Inventaris">Pembelian Inventaris</option>
                                        <option value="Pemasaran">Pemasaran</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="jumlah" class="form-label fw-bold">Jumlah (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah"
                                        step="0.01" min="0.01" placeholder="Cth: 500000.00" required>
                                    <div class="form-text">Masukkan jumlah dengan format desimal (cth: 500000.00).</div>
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label fw-bold">Keterangan/Deskripsi <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                        placeholder="Contoh: Pembelian perlengkapan kebersihan kamar" required></textarea>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" name="submit_pengeluaran" id="submit-button" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i> Simpan Pengeluaran Baru
                                    </button>
                                    <button type="button" onclick="showReadView()" class="btn btn-secondary mt-2">
                                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                                    </button>
                                </div>
                            </form>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- Data Dummy ---
        const dummyData = [
            { id: 1, jenis_pengeluaran: 'variabel', tanggal: '2025-11-20', kategori: 'Pembelian Inventaris', keterangan: 'Pembelian 10 set sprei baru', jumlah: 5500000.00, periode: null },
            { id: 2, jenis_pengeluaran: 'tetap', tanggal: null, kategori: 'Gaji Karyawan', keterangan: 'Gaji Bulanan Staf', jumlah: 10000000.00, periode: 'Bulanan' },
            { id: 3, jenis_pengeluaran: 'variabel', tanggal: '2025-11-22', kategori: 'Operasional', keterangan: 'Beli galon air minum', jumlah: 50000.00, periode: null },
            { id: 4, jenis_pengeluaran: 'tetap', tanggal: null, kategori: 'Perawatan & Perbaikan', keterangan: 'Tagihan Internet dan Telepon', jumlah: 4900000.00, periode: 'Bulanan' },
            { id: 5, jenis_pengeluaran: 'variabel', tanggal: '2025-11-23', kategori: 'Lain-lain', keterangan: 'Biaya fotokopi dokumen', jumlah: 50000.00, periode: null },
        ];
        
        // --- Fungsi Helper Format Rupiah ---
        function formatRupiah(number) {
            return number.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        }

        // --- Fungsi Toggle Input Tanggal/Periode ---
        function toggleInputs(jenis) {
            const tanggalContainer = document.getElementById('tanggal_container');
            const tanggalInput = document.getElementById('tanggal');
            const tanggalRequired = document.getElementById('tanggal_required');
            const periodeContainer = document.getElementById('periode_container');
            const periodeInput = document.getElementById('periode');
            const periodeRequired = document.getElementById('periode_required');

            if (jenis === 'tetap') {
                tanggalContainer.style.display = 'none';
                tanggalInput.removeAttribute('required');
                if (tanggalRequired) tanggalRequired.style.display = 'none';
                tanggalInput.value = ''; // Kosongkan nilai tanggal untuk pengeluaran tetap

                periodeContainer.style.display = 'block';
                periodeInput.setAttribute('required', 'required');
                if (periodeRequired) periodeRequired.style.display = 'inline';
            } else { // variabel
                tanggalContainer.style.display = 'block';
                tanggalInput.setAttribute('required', 'required');
                if (tanggalRequired) tanggalRequired.style.display = 'inline';
                if (!tanggalInput.value) {
                    tanggalInput.value = new Date().toISOString().substring(0, 10); // Isi tanggal hari ini jika kosong
                }

                periodeContainer.style.display = 'none';
                periodeInput.removeAttribute('required');
                if (periodeRequired) periodeRequired.style.display = 'none';
                periodeInput.value = ''; // Kosongkan nilai periode untuk pengeluaran variabel
            }
        }

        // --- Fungsi Tampilkan Daftar (READ) ---
        function showReadView() {
            document.getElementById('read-view').style.display = 'block';
            document.getElementById('form-view').style.display = 'none';
            document.getElementById('page-title').textContent = 'Daftar Pengeluaran';
            document.getElementById('header-title').textContent = 'Daftar Pengeluaran';
            populateTable();
        }

        // --- Fungsi Tampilkan Form (ADD/EDIT) ---
        function showForm(action, id = null) {
            const form = document.getElementById('pengeluaran-form');
            const submitButton = document.getElementById('submit-button');

            document.getElementById('read-view').style.display = 'none';
            document.getElementById('form-view').style.display = 'block';

            if (action === 'add') {
                document.getElementById('page-title').textContent = 'Tambah Pengeluaran Baru';
                document.getElementById('header-title').textContent = 'Tambah Pengeluaran Baru';
                submitButton.textContent = 'Simpan Pengeluaran Baru';
                submitButton.classList.remove('btn-warning');
                submitButton.classList.add('btn-primary');
                // Reset Form
                form.reset();
                document.getElementById('form-action').value = 'add';
                document.getElementById('form-id').value = '';
                document.getElementById('tanggal').value = new Date().toISOString().substring(0, 10);
                toggleInputs('variabel'); // Default ke variabel
            } else if (action === 'edit' && id) {
                const data = dummyData.find(d => d.id === id);
                if (!data) {
                    alert('Data tidak ditemukan!');
                    showReadView();
                    return;
                }
                
                document.getElementById('page-title').textContent = 'Edit Pengeluaran';
                document.getElementById('header-title').textContent = 'Edit Pengeluaran';
                submitButton.textContent = 'Perbarui Pengeluaran';
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-warning');
                
                // Isi Form
                document.getElementById('form-action').value = 'edit';
                document.getElementById('form-id').value = id;
                document.getElementById('jenis_pengeluaran').value = data.jenis_pengeluaran;
                document.getElementById('tanggal').value = data.tanggal || '';
                document.getElementById('periode').value = data.periode || '';
                document.getElementById('kategori').value = data.kategori;
                document.getElementById('jumlah').value = data.jumlah;
                document.getElementById('keterangan').value = data.keterangan;

                toggleInputs(data.jenis_pengeluaran); // Tampilkan/sembunyikan input yang relevan
            }
        }

        // --- Fungsi Isi Tabel Daftar Pengeluaran ---
        function populateTable() {
            const tableBody = document.getElementById('pengeluaran-table-body');
            tableBody.innerHTML = '';
            let total = 0;
            
            dummyData.forEach((data, index) => {
                total += data.jumlah;

                const row = tableBody.insertRow();

                const formattedTanggal = data.tanggal ? new Date(data.tanggal).toLocaleDateString('id-ID') : '-';
                const tipeBadge = `<span class="badge bg-${data.jenis_pengeluaran === 'tetap' ? 'success' : 'info'}">${data.jenis_pengeluaran.charAt(0).toUpperCase() + data.jenis_pengeluaran.slice(1)}</span>`;
                const kategoriBadge = `<span class="badge bg-secondary">${data.kategori}</span>`;

                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${tipeBadge}</td>
                    <td>${formattedTanggal}</td>
                    <td>${kategoriBadge}</td>
                    <td>${data.keterangan}</td>
                    <td>${data.periode || '-'}</td>
                    <td class="text-end">${formatRupiah(data.jumlah)}</td>
                    <td class="text-center">
                        <button onclick="showForm('edit', ${data.id})" class="btn btn-sm btn-warning me-2 text-white" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteData(${data.id})" class="btn btn-sm btn-danger" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
            });

            document.getElementById('total-pengeluaran').textContent = formatRupiah(total);
            
            if (dummyData.length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Belum ada data pengeluaran yang tercatat.</td></tr>';
            }
        }

        // --- Fungsi Dummy Aksi Delete ---
        function deleteData(id) {
            if (confirm(`Apakah Anda yakin ingin menghapus pengeluaran dengan ID ${id}?`)) {
                // Simulasi aksi hapus
                const index = dummyData.findIndex(d => d.id === id);
                if (index > -1) {
                    dummyData.splice(index, 1);
                    document.getElementById('message-area').innerHTML = `<div class='alert alert-success'>Pengeluaran dengan ID ${id} berhasil dihapus. (Simulasi)</div>`;
                    populateTable();
                }
            }
        }
        
        // --- Event Listener untuk Submit Form (Simulasi) ---
        document.getElementById('pengeluaran-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const action = document.getElementById('form-action').value;
            const id = document.getElementById('form-id').value;
            const jenis = document.getElementById('jenis_pengeluaran').value;
            const keterangan = document.getElementById('keterangan').value;
            
            const message = action === 'add' 
                ? `Data pengeluaran "${keterangan}" berhasil ditambahkan! (Simulasi)`
                : `Data pengeluaran "${keterangan}" berhasil diperbarui! (Simulasi)`;

            // Logika sederhana untuk simulasi penyimpanan (hanya untuk menunjukkan flow)
            if (action === 'add') {
                 // Di sini Anda akan menambahkan data baru ke array dummyData
            } else if (action === 'edit') {
                 // Di sini Anda akan memperbarui data di array dummyData
            }

            document.getElementById('message-area').innerHTML = `<div class='alert alert-success'>${message}</div>`;
            showReadView(); // Kembali ke tampilan daftar
        });


        // --- Inisialisasi Saat Dokumen Selesai Dimuat ---
        document.addEventListener('DOMContentLoaded', function() {
            populateTable();
            // Inisialisasi fungsi toggleInputs saat load
            const jenisSelect = document.getElementById('jenis_pengeluaran');
            if (jenisSelect) {
                toggleInputs(jenisSelect.value);
            }
        });
    </script>
</body>

</html>