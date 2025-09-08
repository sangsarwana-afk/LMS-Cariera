<?php
session_start();
require_once '../config/database.php';
require_once '../src/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = "Dashboard - Sistem Perpustakaan";
include '../src/header.php';
?>

<div class="container">
    <div class="dashboard">
        <h1>Dashboard Sistem Perpustakaan</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Buku</h3>
                <p class="stat-number" id="total-books">0</p>
            </div>
            <div class="stat-card">
                <h3>Buku Dipinjam</h3>
                <p class="stat-number" id="borrowed-books">0</p>
            </div>
            <div class="stat-card">
                <h3>Buku Tersedia</h3>
                <p class="stat-number" id="available-books">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Anggota</h3>
                <p class="stat-number" id="total-members">0</p>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Menu Utama</h2>
            <div class="action-grid">
                <a href="books.php" class="action-card">
                    <h3>Kelola Buku</h3>
                    <p>Tambah, edit, dan hapus data buku</p>
                </a>
                <a href="loans.php" class="action-card">
                    <h3>Kelola Peminjaman</h3>
                    <p>Proses peminjaman dan pengembalian</p>
                </a>
                <a href="members.php" class="action-card">
                    <h3>Kelola Anggota</h3>
                    <p>Manajemen data anggota perpustakaan</p>
                </a>
                <a href="reports.php" class="action-card">
                    <h3>Laporan</h3>
                    <p>Lihat laporan statistik perpustakaan</p>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Load dashboard statistics
document.addEventListener('DOMContentLoaded', function() {
    fetch('../src/api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-books').textContent = data.total_books;
            document.getElementById('borrowed-books').textContent = data.borrowed_books;
            document.getElementById('available-books').textContent = data.available_books;
            document.getElementById('total-members').textContent = data.total_members;
        })
        .catch(error => console.error('Error loading stats:', error));
});
</script>

<?php include '../src/footer.php'; ?>
