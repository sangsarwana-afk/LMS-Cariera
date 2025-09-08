// Book management JavaScript

// Modal functions
function openAddBookModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Buku';
    document.getElementById('bookForm').reset();
    document.getElementById('bookModal').style.display = 'block';
}

function closeBookModal() {
    document.getElementById('bookModal').style.display = 'none';
}

function editBook(bookId) {
    // TODO: Load book data and populate form
    document.getElementById('modalTitle').textContent = 'Edit Buku';
    document.getElementById('bookModal').style.display = 'block';
}

function deleteBook(bookId) {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
        // TODO: Implement delete functionality
        console.log('Delete book:', bookId);
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBooks');
    const table = document.getElementById('booksTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const title = rows[i].cells[1].textContent.toLowerCase();
                const author = rows[i].cells[2].textContent.toLowerCase();
                
                if (title.includes(filter) || author.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }
});

// Form submission
document.getElementById('bookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // TODO: Implement form submission via AJAX
    console.log('Form submitted');
    
    // Close modal after submission
    closeBookModal();
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookModal');
    if (event.target === modal) {
        closeBookModal();
    }
}
