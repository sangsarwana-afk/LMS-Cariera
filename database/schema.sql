-- Library Management System Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- Staff table for librarians and administrators
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'librarian') NOT NULL DEFAULT 'librarian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Members table for library members
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    membership_date DATE NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table for book categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(200) NOT NULL,
    publisher VARCHAR(100),
    publication_year YEAR,
    category_id INT,
    pages INT,
    language VARCHAR(50) DEFAULT 'Indonesia',
    location VARCHAR(50),
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Loans table for tracking book borrowing
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    staff_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Fiksi', 'Novel, cerpen, dan karya fiksi lainnya'),
('Non-Fiksi', 'Biografi, sejarah, dan karya faktual'),
('Sains & Teknologi', 'Buku tentang ilmu pengetahuan dan teknologi'),
('Pendidikan', 'Buku pelajaran dan referensi akademik'),
('Anak-anak', 'Buku untuk anak-anak dan remaja'),
('Agama & Filosofi', 'Buku tentang agama dan filosofi'),
('Seni & Budaya', 'Buku tentang seni, musik, dan budaya');

-- Insert default admin user (password: admin123)
INSERT INTO staff (username, password, name, email, role) VALUES
('admin', MD5('admin123'), 'Administrator', 'admin@library.com', 'admin');

-- Insert sample librarian (password: librarian123)
INSERT INTO staff (username, password, name, email, role) VALUES
('librarian', MD5('librarian123'), 'Petugas Perpustakaan', 'librarian@library.com', 'librarian');

-- Insert sample members
INSERT INTO members (member_id, name, email, phone, address, membership_date) VALUES
('M001', 'Ahmad Wijaya', 'ahmad@email.com', '081234567890', 'Jl. Merdeka No. 123', '2025-01-01'),
('M002', 'Siti Nurhaliza', 'siti@email.com', '081234567891', 'Jl. Sudirman No. 456', '2025-01-02'),
('M003', 'Budi Santoso', 'budi@email.com', '081234567892', 'Jl. Thamrin No. 789', '2025-01-03');

-- Insert sample books
INSERT INTO books (isbn, title, author, publisher, publication_year, category_id, total_copies, available_copies, location) VALUES
('978-602-291-130-9', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 1, 3, 3, 'A-001'),
('978-979-22-3186-2', 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 1, 2, 2, 'A-002'),
('978-602-8519-93-9', 'Filosofi Teras', 'Henry Manampiring', 'Kompas Gramedia', 2018, 6, 5, 5, 'B-001'),
('978-979-22-0578-8', 'Sejarah Indonesia Modern', 'MC Ricklefs', 'Gadjah Mada University Press', 2008, 2, 2, 2, 'C-001');

-- Create indexes for better performance
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_members_member_id ON members(member_id);
CREATE INDEX idx_loans_status ON loans(status);
CREATE INDEX idx_loans_due_date ON loans(due_date);
