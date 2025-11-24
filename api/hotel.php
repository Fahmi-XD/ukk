<?php
header('Content-Type: application/json');

require_once("../koneksi.php");

// Ambil parameter filter dari GET
// Menggunakan operator null coalescing (??) untuk nilai default jika tidak ada
$capacity = $_GET['capacity'] ?? '';
$max_price = $_GET['price'] ?? '';
$search_term = $_GET['search'] ?? '';
$location_home = isset($_GET["location"]) && $_GET["location"] == "home";

// Base SQL query
$select_fields = $location_home
    ? "rt.*, (SELECT r.image FROM rooms r WHERE r.room_type_id = rt.id LIMIT 1) as image, (SELECT COUNT(*) FROM rooms r WHERE r.room_type_id = rt.id AND r.status = 'available') as available_rooms"
    : "rt.*, (SELECT r.image FROM rooms r WHERE r.room_type_id = rt.id LIMIT 1) as room_image, (SELECT COUNT(*) FROM rooms r WHERE r.room_type_id = rt.id AND r.status = 'available') as available_rooms";

$sql = "SELECT {$select_fields} FROM room_types rt";

$where_clauses = [];
$params = [];
$param_types = "";

// 1. Filter Kapasitas (capacity)
if ($capacity !== '') {
    $capacity_val = (int)$capacity;
    if ($capacity_val >= 5) {
        // Untuk 5+ orang, cari kapasitas 5 atau lebih
        $where_clauses[] = "rt.capacity >= ?";
    } else {
        // Untuk kapasitas spesifik 1, 2, 3, 4
        $where_clauses[] = "rt.capacity = ?";
    }
    $param_types .= "i"; // i for integer
    $params[] = $capacity_val;
}

// 2. Filter Harga Maksimum (price_per_night)
// Menggunakan kolom DB yang benar: 'price_per_night'
if ($max_price !== '') {
    $max_price_val = (float)$max_price;
    $where_clauses[] = "rt.price_per_night <= ?";
    $param_types .= "d"; // d for double/decimal
    $params[] = $max_price_val;
}

// 3. Filter Pencarian (name atau facilities)
if ($search_term !== '') {
    $search_like = "%" . $search_term . "%";
    // Cari di nama TIPE atau FASILITAS
    $where_clauses[] = "(rt.name LIKE ? OR rt.facilities LIKE ?)";
    $param_types .= "ss"; // s for string
    $params[] = $search_like;
    $params[] = $search_like;
}

// Gabungkan klausa WHERE jika ada filter
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Urutan dan Batas
$sql .= " ORDER BY rt.price_per_night ASC";

if ($location_home) {
    $sql .= " LIMIT 6";
}

// Persiapkan dan eksekusi query (Prepared Statement)
$stmt = $koneksi->prepare($sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "SQL Prepare Error: " . $koneksi->error]);
    $koneksi->close();
    exit;
}

// Binding parameters secara dinamis
if (!empty($params)) {
    // Membuat array argumen untuk bind_param: ['sdi', $param1, $param2, $param3]
    $bind_args = array_merge([$param_types], $params);
    $stmt->bind_param(...$bind_args);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Logika untuk menangani image string menjadi array
        $image_col = $location_home ? "image" : "room_image";
        $row[$image_col] = $row[$image_col] !== null ? explode(",", $row[$image_col]) : null;
        $data[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "count" => count($data),
        "data" => $data
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "empty",
        "message" => "Tidak ada data kamar yang ditemukan."
    ], JSON_PRETTY_PRINT);
}

$koneksi->close();
