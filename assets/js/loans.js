// Loans management JavaScript

// Tab functionality
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    
    // Hide all tab content
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    
    // Remove active class from all tab buttons
    tablinks = document.getElementsByClassName("tab-button");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    
    // Show the selected tab and mark button as active
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Modal functions
function openNewLoanModal() {
    document.getElementById('loanForm').reset();
    document.getElementById('loanModal').style.display = 'block';
}

function closeLoanModal() {
    document.getElementById('loanModal').style.display = 'none';
}

function returnBook(loanId) {
    if (confirm('Apakah buku sudah dikembalikan?')) {
        // TODO: Implement return book functionality
        console.log('Return book for loan:', loanId);
    }
}

// Search functionality for active loans
document.addEventListener('DOMContentLoaded', function() {
    const searchActive = document.getElementById('searchActiveLoans');
    const activeTable = document.getElementById('activeLoansTable');
    
    if (searchActive && activeTable) {
        searchActive.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = activeTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const memberName = rows[i].cells[1].textContent.toLowerCase();
                const bookTitle = rows[i].cells[2].textContent.toLowerCase();
                
                if (memberName.includes(filter) || bookTitle.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }

    // Search functionality for recent returns
    const searchReturns = document.getElementById('searchRecentReturns');
    const returnsTable = document.getElementById('recentReturnsTable');
    
    if (searchReturns && returnsTable) {
        searchReturns.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = returnsTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const memberName = rows[i].cells[1].textContent.toLowerCase();
                const bookTitle = rows[i].cells[2].textContent.toLowerCase();
                
                if (memberName.includes(filter) || bookTitle.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }
});

// Member search functionality
function setupMemberSearch() {
    const memberSearch = document.getElementById('member_search');
    const memberResults = document.getElementById('member_results');
    
    if (memberSearch) {
        memberSearch.addEventListener('input', function() {
            const query = this.value;
            if (query.length >= 2) {
                // TODO: Implement AJAX search for members
                console.log('Search members:', query);
            } else {
                memberResults.style.display = 'none';
            }
        });
    }
}

// Book search functionality
function setupBookSearch() {
    const bookSearch = document.getElementById('book_search');
    const bookResults = document.getElementById('book_results');
    
    if (bookSearch) {
        bookSearch.addEventListener('input', function() {
            const query = this.value;
            if (query.length >= 2) {
                // TODO: Implement AJAX search for available books
                console.log('Search books:', query);
            } else {
                bookResults.style.display = 'none';
            }
        });
    }
}

// Form submission
document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    const memberId = document.getElementById('selected_member_id').value;
    const bookId = document.getElementById('selected_book_id').value;
    
    if (!memberId) {
        alert('Silakan pilih anggota terlebih dahulu');
        return;
    }
    
    if (!bookId) {
        alert('Silakan pilih buku terlebih dahulu');
        return;
    }
    
    // TODO: Implement form submission via AJAX
    console.log('Loan form submitted');
    
    // Close modal after submission
    closeLoanModal();
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loanModal');
    if (event.target === modal) {
        closeLoanModal();
    }
}

// Initialize search functions
document.addEventListener('DOMContentLoaded', function() {
    setupMemberSearch();
    setupBookSearch();
});
