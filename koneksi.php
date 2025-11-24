<?php
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hotel_booking';

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);
?>