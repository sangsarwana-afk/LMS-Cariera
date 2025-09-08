<?php
session_start();
require_once '../config/database.php';
require_once '../src/auth.php';

requireLogin();
$page_title = "Kelola Buku - Sistem Perpustakaan";
include '../src/header.php';

$db = getDB();

// Get all books with category information
$stmt = $db->prepare("
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    ORDER BY b.title
");
$stmt->execute();
$books = $stmt->fetchAll();

// Get all categories for the form
$stmt = $db->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<div class="container">
    <div class="page-header">
        <h1>Kelola Buku</h1>
        <button class="btn btn-primary" onclick="openAddBookModal()">Tambah Buku</button>
    </div>

    <div class="search-bar">
        <input type="text" id="searchBooks" placeholder="Cari buku berdasarkan judul atau pengarang...">
    </div>

    <div class="books-table">
        <table id="booksTable">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Tersedia</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['isbn'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['category_name'] ?? 'Tanpa Kategori') ?></td>
                    <td><?= $book['total_copies'] ?></td>
                    <td><?= $book['available_copies'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editBook(<?= $book['id'] ?>)">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteBook(<?= $book['id'] ?>)">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Book Modal -->
<div id="bookModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Buku</h2>
            <span class="close" onclick="closeBookModal()">&times;</span>
        </div>
        <form id="bookForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn">
                </div>
                <div class="form-group">
                    <label for="title">Judul Buku:</label>
                    <input type="text" id="title" name="title" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="author">Pengarang:</label>
                    <input type="text" id="author" name="author" required>
                </div>
                <div class="form-group">
                    <label for="publisher">Penerbit:</label>
                    <input type="text" id="publisher" name="publisher">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="publication_year">Tahun Terbit:</label>
                    <input type="number" id="publication_year" name="publication_year" min="1900" max="2025">
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori:</label>
                    <select id="category_id" name="category_id">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="total_copies">Jumlah Eksemplar:</label>
                    <input type="number" id="total_copies" name="total_copies" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label for="location">Lokasi:</label>
                    <input type="text" id="location" name="location" placeholder="Contoh: A-001">
                </div>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeBookModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/books.js"></script>

<?php include '../src/footer.php'; ?>
