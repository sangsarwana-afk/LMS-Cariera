<?php
session_start();
require_once '../config/database.php';
require_once '../src/auth.php';

requireLogin();
$page_title = "Kelola Peminjaman - Sistem Perpustakaan";
include '../src/header.php';

$db = getDB();

// Get active loans
$stmt = $db->prepare("
    SELECT l.*, m.name as member_name, m.member_id, b.title as book_title, b.author as book_author,
           s.name as staff_name
    FROM loans l
    JOIN members m ON l.member_id = m.id
    JOIN books b ON l.book_id = b.id
    JOIN staff s ON l.staff_id = s.id
    WHERE l.status IN ('borrowed', 'overdue')
    ORDER BY l.loan_date DESC
");
$stmt->execute();
$active_loans = $stmt->fetchAll();

// Get recent returned loans
$stmt = $db->prepare("
    SELECT l.*, m.name as member_name, m.member_id, b.title as book_title, b.author as book_author,
           s.name as staff_name
    FROM loans l
    JOIN members m ON l.member_id = m.id
    JOIN books b ON l.book_id = b.id
    JOIN staff s ON l.staff_id = s.id
    WHERE l.status = 'returned'
    ORDER BY l.return_date DESC
    LIMIT 20
");
$stmt->execute();
$recent_returns = $stmt->fetchAll();
?>

<div class="container">
    <div class="page-header">
        <h1>Kelola Peminjaman</h1>
        <button class="btn btn-primary" onclick="openNewLoanModal()">Peminjaman Baru</button>
    </div>

    <div class="tabs">
        <button class="tab-button active" onclick="openTab(event, 'activeLoanTab')">Sedang Dipinjam</button>
        <button class="tab-button" onclick="openTab(event, 'recentReturnsTab')">Baru Dikembalikan</button>
    </div>

    <!-- Active Loans Tab -->
    <div id="activeLoanTab" class="tab-content active">
        <div class="search-bar">
            <input type="text" id="searchActiveLoans" placeholder="Cari berdasarkan nama anggota atau judul buku...">
        </div>
        
        <div class="loans-table">
            <table id="activeLoansTable">
                <thead>
                    <tr>
                        <th>ID Anggota</th>
                        <th>Nama Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_loans as $loan): ?>
                    <tr class="<?= $loan['status'] === 'overdue' ? 'overdue-row' : '' ?>">
                        <td><?= htmlspecialchars($loan['member_id']) ?></td>
                        <td><?= htmlspecialchars($loan['member_name']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($loan['book_title']) ?></strong><br>
                            <small>oleh <?= htmlspecialchars($loan['book_author']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($loan['due_date'])) ?></td>
                        <td>
                            <span class="status-badge status-<?= $loan['status'] ?>">
                                <?= $loan['status'] === 'borrowed' ? 'Dipinjam' : 'Terlambat' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="returnBook(<?= $loan['id'] ?>)">Kembalikan</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Returns Tab -->
    <div id="recentReturnsTab" class="tab-content">
        <div class="search-bar">
            <input type="text" id="searchRecentReturns" placeholder="Cari berdasarkan nama anggota atau judul buku...">
        </div>
        
        <div class="loans-table">
            <table id="recentReturnsTable">
                <thead>
                    <tr>
                        <th>ID Anggota</th>
                        <th>Nama Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_returns as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan['member_id']) ?></td>
                        <td><?= htmlspecialchars($loan['member_name']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($loan['book_title']) ?></strong><br>
                            <small>oleh <?= htmlspecialchars($loan['book_author']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($loan['return_date'])) ?></td>
                        <td>
                            <?= $loan['fine_amount'] > 0 ? 'Rp ' . number_format($loan['fine_amount'], 0, ',', '.') : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Loan Modal -->
<div id="loanModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Peminjaman Baru</h2>
            <span class="close" onclick="closeLoanModal()">&times;</span>
        </div>
        <form id="loanForm">
            <div class="form-group">
                <label for="member_search">Cari Anggota:</label>
                <input type="text" id="member_search" placeholder="Masukkan ID atau nama anggota...">
                <div id="member_results" class="search-results"></div>
                <input type="hidden" id="selected_member_id" name="member_id">
            </div>
            
            <div class="form-group">
                <label for="book_search">Cari Buku:</label>
                <input type="text" id="book_search" placeholder="Masukkan judul atau pengarang...">
                <div id="book_results" class="search-results"></div>
                <input type="hidden" id="selected_book_id" name="book_id">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="loan_date">Tanggal Pinjam:</label>
                    <input type="date" id="loan_date" name="loan_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="due_date">Tanggal Jatuh Tempo:</label>
                    <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Catatan (Opsional):</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeLoanModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Proses Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/loans.js"></script>

<?php include '../src/footer.php'; ?>
