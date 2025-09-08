# Sistem Perpustakaan (Library Management System)

Aplikasi web berbasis PHP untuk mengelola perpustakaan, memungkinkan petugas untuk memasukkan data buku dan mengelola peminjaman buku dengan antarmuka yang mudah digunakan.

## 🚀 Fitur Utama

### 📚 Manajemen Buku
- Tambah, edit, dan hapus data buku
- Pencarian buku berdasarkan judul atau pengarang
- Kategorisasi buku
- Tracking stok dan ketersediaan buku
- Informasi lengkap buku (ISBN, penerbit, tahun terbit, dll.)

### 👥 Manajemen Anggota
- Pendaftaran anggota baru
- Kelola data anggota perpustakaan
- Status keanggotaan (aktif/non-aktif)

### 📖 Sistem Peminjaman
- Proses peminjaman buku
- Tracking buku yang sedang dipinjam
- Pengembalian buku
- Sistem denda untuk keterlambatan
- Histori peminjaman

### 📊 Dashboard & Laporan
- Dashboard dengan statistik perpustakaan
- Laporan peminjaman
- Status real-time ketersediaan buku

### 🔐 Sistem Autentikasi
- Login untuk petugas perpustakaan
- Role-based access (Admin/Librarian)
- Session management

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS dengan desain responsif

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- PDO extension untuk PHP

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd library-management-system
```

### 2. Konfigurasi Database
1. Buat database MySQL baru:
```sql
CREATE DATABASE library_management;
```

2. Import skema database:
```bash
mysql -u your_username -p library_management < database/schema.sql
```

3. Update konfigurasi database di `config/database.php`:
```php
$host = 'localhost';
$dbname = 'library_management';
$username = 'your_username';
$password = 'your_password';
```

### 3. Setup Web Server
1. Copy folder project ke web server directory (htdocs untuk XAMPP)
2. Akses aplikasi melalui browser: `http://localhost/library-management-system/public/`

### 4. Login Default
- **Admin**:
  - Username: `admin`
  - Password: `admin123`
- **Librarian**:
  - Username: `librarian`
  - Password: `librarian123`

## 📁 Struktur Project

```
library-management-system/
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet utama
│   ├── js/                    # JavaScript files
│   └── images/                # Gambar dan assets
├── config/
│   └── database.php           # Konfigurasi database
├── database/
│   └── schema.sql             # Skema database
├── public/
│   ├── index.php              # Dashboard utama
│   ├── login.php              # Halaman login
│   ├── books.php              # Manajemen buku
│   ├── loans.php              # Manajemen peminjaman
│   ├── members.php            # Manajemen anggota
│   └── reports.php            # Laporan
├── src/
│   ├── auth.php               # Sistem autentikasi
│   ├── header.php             # Template header
│   ├── footer.php             # Template footer
│   └── api/                   # API endpoints
└── README.md
```

## 🎯 Cara Penggunaan

### Login
1. Buka aplikasi di browser
2. Masukkan username dan password
3. Klik Login untuk mengakses dashboard

### Mengelola Buku
1. Klik menu "Kelola Buku" dari dashboard
2. Klik "Tambah Buku" untuk menambah buku baru
3. Isi informasi lengkap buku
4. Simpan data buku

### Proses Peminjaman
1. Klik menu "Kelola Peminjaman"
2. Klik "Peminjaman Baru"
3. Cari dan pilih anggota
4. Cari dan pilih buku yang akan dipinjam
5. Tentukan tanggal jatuh tempo
6. Proses peminjaman

### Pengembalian Buku
1. Di halaman "Kelola Peminjaman", tab "Sedang Dipinjam"
2. Cari peminjaman yang akan dikembalikan
3. Klik tombol "Kembalikan"
4. Konfirmasi pengembalian

## 🔧 Konfigurasi

### Database
Edit file `config/database.php` untuk menyesuaikan pengaturan database:
- Host database
- Nama database
- Username dan password

### Pengaturan Aplikasi
- Durasi peminjaman default: 14 hari (dapat diubah di form peminjaman)
- Sistem denda: Otomatis berdasarkan keterlambatan

## 📊 Database Schema

### Tabel Utama:
- **staff**: Data petugas perpustakaan
- **members**: Data anggota perpustakaan
- **categories**: Kategori buku
- **books**: Data buku
- **loans**: Data peminjaman

### Relasi Database:
- Books → Categories (many-to-one)
- Loans → Members (many-to-one)
- Loans → Books (many-to-one)
- Loans → Staff (many-to-one)

## 🚀 Development

### Menambah Fitur Baru
1. Buat file PHP baru di folder `public/`
2. Gunakan template header dan footer dari folder `src/`
3. Tambahkan styling di `assets/css/style.css`
4. Update navigasi di `src/header.php`

### Database Migration
Untuk menambah tabel atau kolom baru:
1. Buat file SQL di folder `database/`
2. Update skema utama di `schema.sql`
3. Dokumentasikan perubahan

## 🤝 Contributing

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 License

Project ini adalah open source dan tersedia under [MIT License](LICENSE).

## 🐛 Bug Reports

Jika menemukan bug atau ingin request fitur baru, silakan buat issue di repository ini dengan detail:
- Deskripsi masalah
- Steps to reproduce
- Expected behavior
- Screenshots (jika ada)
- Environment details

## 📞 Support

Untuk pertanyaan atau bantuan, silakan:
- Buat issue di GitHub repository
- Email: [your-email@domain.com]

## 🎉 Acknowledgments

- PHP Community
- Bootstrap CSS Framework inspiration
- MySQL Documentation
- Contributors dan testers

---

**Dibuat dengan ❤️ untuk memudahkan manajemen perpustakaan**
