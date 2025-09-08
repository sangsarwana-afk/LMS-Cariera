<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Sistem Perpustakaan' ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <h1>Sistem Perpustakaan</h1>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Dashboard</a>
                <a href="books.php" class="nav-link">Buku</a>
                <a href="members.php" class="nav-link">Anggota</a>
                <a href="loans.php" class="nav-link">Peminjaman</a>
                <a href="reports.php" class="nav-link">Laporan</a>
            </div>
            <div class="nav-user">
                <span>Halo, <?= getCurrentUser()['name'] ?? 'User' ?></span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </nav>
    </header>
    <main>
